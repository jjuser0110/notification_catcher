<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\Message;
use App\Models\Payin;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /** Gateway overview, tailored to the signed-in user's role. */
    public function index(Request $request): View
    {
        $me = $request->user();

        // Which merchants fall under this user.
        if ($me->isStaffSide()) {
            $merchantIds = User::where('role', User::ROLE_MERCHANT)->pluck('id')->all();
            $customerCount = User::where('role', User::ROLE_CUSTOMER)->count();
        } elseif ($me->isCustomer()) {
            $merchantIds = $me->descendantMerchantIds();
            $customerCount = count(array_filter(
                User::whereIn('id', $me->descendantIds() ?: [0])->pluck('role')->all(),
                fn ($r) => $r === User::ROLE_CUSTOMER,
            ));
        } else { // merchant
            $merchantIds = [$me->id];
            $customerCount = 0;
        }
        $merchantIds = $merchantIds ?: [0];

        $payinToday = Payin::whereIn('user_id', $merchantIds)
            ->where('status', Payin::STATUS_SUCCESS)
            ->whereDate('approved_date', today())
            ->sum('amount');

        $stats = [
            'payin_wallet' => $me->payin_wallet,
            'payout_wallet' => $me->payout_wallet,
            'all_wallet' => $me->all_wallet,
            'quota' => $me->quota,
            'merchants' => count($merchantIds) === 1 && $merchantIds[0] === 0 ? 0 : count($merchantIds),
            'customers' => $customerCount,
            'payin_today' => $payinToday,
            'pending_payins' => Payin::whereIn('user_id', $merchantIds)->where('status', Payin::STATUS_PENDING)->count(),
            'pending_settlements' => SettlementController::pendingCountFor($me),
        ];

        return view('dashboard', compact('me', 'stats'));
    }

    /** Notification Catcher — installed phones. */
    public function devices(): View
    {
        $devices = Device::withCount('messages')->orderByDesc('last_seen_at')->get();

        $deviceStats = [
            'devices' => $devices->count(),
            'online' => $devices->filter->isOnline()->count(),
            'notifications' => Message::where('type', 'notification')->count(),
            'sms' => Message::where('type', 'sms')->count(),
        ];

        return view('devices', compact('devices', 'deviceStats'));
    }

    public function messages(Request $request): View
    {
        $filters = $request->validate([
            'device' => ['nullable', 'integer', 'exists:devices,id'],
            'type' => ['nullable', 'in:notification,sms'],
            'q' => ['nullable', 'string', 'max:255'],
        ]);

        $messages = Message::with('device')
            ->when($filters['device'] ?? null, fn ($query, $id) => $query->where('device_id', $id))
            ->when($filters['type'] ?? null, fn ($query, $type) => $query->where('type', $type))
            ->when($filters['q'] ?? null, function ($query, $term) {
                $query->where(function ($inner) use ($term) {
                    $inner->where('title', 'like', "%{$term}%")
                        ->orWhere('body', 'like', "%{$term}%")
                        ->orWhere('sender', 'like', "%{$term}%")
                        ->orWhere('app_name', 'like', "%{$term}%");
                });
            })
            ->orderByDesc('received_at')
            ->paginate(50)
            ->withQueryString();

        $devices = Device::orderBy('name')->get();

        return view('messages', compact('messages', 'devices', 'filters'));
    }
}
