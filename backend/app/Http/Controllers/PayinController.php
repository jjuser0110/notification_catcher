<?php

namespace App\Http\Controllers;

use App\Models\Payin;
use App\Models\User;
use App\Services\PayinService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PayinController extends Controller
{
    public function index(Request $request): View
    {
        $query = Payin::with('merchant', 'bankSetting.bank')->latest('id');
        $this->scope($query, $request->user());

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }
        if ($search = $request->query('q')) {
            $query->where(fn ($w) => $w->where('transaction_no', 'like', "%{$search}%")
                ->orWhere('reference_no', 'like', "%{$search}%")
                ->orWhere('player', 'like', "%{$search}%"));
        }

        return view('payins.index', [
            'payins' => $query->paginate(30)->withQueryString(),
            'status' => $status,
        ]);
    }

    public function show(Request $request, Payin $payin): View
    {
        $this->authorizeView($request->user(), $payin);

        return view('payins.show', [
            'payin' => $payin->load('merchant', 'bankSetting.bank', 'logs.user', 'manualUpdateBy'),
        ]);
    }

    /** Staff-side manual approval: mark success and split to wallets. */
    public function approve(Request $request, Payin $payin): RedirectResponse
    {
        abort_unless($request->user()->isStaffSide(), 403);

        if (! $payin->isSuccess()) {
            $payin->forceFill([
                'status' => Payin::STATUS_SUCCESS,
                'approved_date' => now(),
                'rate' => $payin->merchant->effectivePayinRate(),
                'nett_amount' => round((float) $payin->amount * $payin->merchant->effectivePayinRate() / 100, 2),
                'manual_update_by' => $request->user()->id,
            ])->save();

            PayinService::distribute($payin);
        }

        return back()->with('ok', 'Payin '.$payin->transaction_no.' approved and split to wallets.');
    }

    private function scope($query, User $user): void
    {
        if ($user->isStaffSide()) {
            return;
        }
        if ($user->isCustomer()) {
            $query->whereIn('user_id', $user->descendantMerchantIds() ?: [0]);
            return;
        }
        // merchant
        $query->where('user_id', $user->id);
    }

    private function authorizeView(User $user, Payin $payin): void
    {
        if ($user->isStaffSide()) return;
        if ($user->isMerchant()) {
            abort_unless($payin->user_id === $user->id, 403);
            return;
        }
        abort_unless(in_array($payin->user_id, $user->descendantMerchantIds(), true), 403);
    }
}
