<?php

namespace App\Http\Controllers;

use App\Models\Settlement;
use App\Models\SettlementBank;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SettlementController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $query = Settlement::with('user', 'settlementBank.bank', 'items.merchant')->latest('id');

        if (! $user->isStaffSide()) {
            $query->where('user_id', $user->id);
        }
        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        return view('settlements.index', [
            'settlements' => $query->paginate(10)->withQueryString(),
            'status' => $status,
            'canApprove' => $user->isStaffSide(),
        ]);
    }

    /** Agent (customer) builds a settlement from their merchants' wallets. */
    public function create(Request $request): View
    {
        $user = $request->user();
        abort_unless($user->isCustomer(), 403, 'Only agents can create settlements.');

        $merchants = User::whereIn('id', $user->descendantMerchantIds() ?: [0])
            ->orderBy('name')
            ->get(['id', 'name', 'username', 'payin_wallet', 'payout_wallet']);

        return view('settlements.create', [
            'merchants' => $merchants,
            'settlementBanks' => SettlementBank::where('user_id', $user->id)
                ->where('is_active', true)->with('bank')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user->isCustomer(), 403);

        $request->validate([
            'settlement_bank_id' => ['required', 'exists:settlement_banks,id'],
            'payin' => ['array'],
            'payout' => ['array'],
        ]);

        $bank = SettlementBank::where('user_id', $user->id)->findOrFail($request->input('settlement_bank_id'));
        $allowed = $user->descendantMerchantIds();

        $payin = $request->input('payin', []);
        $payout = $request->input('payout', []);
        $merchantIds = array_unique(array_merge(array_keys($payin), array_keys($payout)));

        $rows = [];
        $totalPayin = 0.0;
        $totalPayout = 0.0;

        foreach ($merchantIds as $mid) {
            if (! in_array((int) $mid, $allowed, true)) continue;
            $pin = round((float) ($payin[$mid] ?? 0), 2);
            $pout = round((float) ($payout[$mid] ?? 0), 2);
            if ($pin <= 0 && $pout <= 0) continue;

            $merchant = User::find($mid);
            if ($pin > (float) $merchant->payin_wallet) {
                throw ValidationException::withMessages([
                    "payin.$mid" => "Payin amount exceeds {$merchant->username}'s wallet.",
                ]);
            }
            if ($pout > (float) $merchant->payout_wallet) {
                throw ValidationException::withMessages([
                    "payout.$mid" => "Payout amount exceeds {$merchant->username}'s wallet.",
                ]);
            }

            $rows[] = compact('merchant', 'pin', 'pout');
            $totalPayin += $pin;
            $totalPayout += $pout;
        }

        if (! $rows) {
            throw ValidationException::withMessages(['settlement_bank_id' => 'Enter at least one amount to settle.']);
        }

        DB::transaction(function () use ($user, $bank, $rows, $totalPayin, $totalPayout) {
            $settlement = Settlement::create([
                'user_id' => $user->id,
                'settlement_bank_id' => $bank->id,
                'payin_amount' => $totalPayin,
                'payout_amount' => $totalPayout,
                'total_amount' => $totalPayin + $totalPayout,
                'status' => Settlement::STATUS_PENDING,
            ]);

            foreach ($rows as $row) {
                $settlement->items()->create([
                    'merchant_id' => $row['merchant']->id,
                    'payin_amount' => $row['pin'],
                    'payout_amount' => $row['pout'],
                ]);

                // Hold the funds: deduct now, refund if rejected.
                $row['merchant']->forceFill([
                    'payin_wallet' => (float) $row['merchant']->payin_wallet - $row['pin'],
                    'payout_wallet' => (float) $row['merchant']->payout_wallet - $row['pout'],
                    'all_wallet' => (float) $row['merchant']->all_wallet - $row['pin'] - $row['pout'],
                ])->save();
            }
        });

        return redirect()->route('settlements.index')->with('ok', 'Settlement submitted and pending approval.');
    }

    public function approve(Request $request, Settlement $settlement): RedirectResponse
    {
        abort_unless($request->user()->isStaffSide(), 403);
        if ($settlement->status === Settlement::STATUS_PENDING) {
            $settlement->forceFill([
                'status' => Settlement::STATUS_APPROVED,
                'approved_by' => $request->user()->id,
                'approved_at' => now(),
            ])->save();
        }

        return back()->with('ok', 'Settlement approved.');
    }

    public function reject(Request $request, Settlement $settlement): RedirectResponse
    {
        abort_unless($request->user()->isStaffSide(), 403);

        if ($settlement->status === Settlement::STATUS_PENDING) {
            DB::transaction(function () use ($settlement, $request) {
                // Refund held funds to each merchant.
                foreach ($settlement->items as $item) {
                    $merchant = $item->merchant;
                    if (! $merchant) continue;
                    $merchant->forceFill([
                        'payin_wallet' => (float) $merchant->payin_wallet + (float) $item->payin_amount,
                        'payout_wallet' => (float) $merchant->payout_wallet + (float) $item->payout_amount,
                        'all_wallet' => (float) $merchant->all_wallet + (float) $item->payin_amount + (float) $item->payout_amount,
                    ])->save();
                }
                $settlement->forceFill([
                    'status' => Settlement::STATUS_REJECTED,
                    'approved_by' => $request->user()->id,
                    'approved_at' => now(),
                    'remark' => $request->input('remark'),
                ])->save();
            });
        }

        return back()->with('ok', 'Settlement rejected and funds refunded.');
    }

    /** Count shown on the sidebar badge. */
    public static function pendingCountFor(User $user): int
    {
        $q = Settlement::where('status', Settlement::STATUS_PENDING);
        if (! $user->isStaffSide()) {
            $q->where('user_id', $user->id);
        }
        return $q->count();
    }
}
