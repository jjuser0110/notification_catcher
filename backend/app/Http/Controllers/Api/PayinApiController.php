<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payin;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Merchant-facing API to create a payin (deposit) order.
 * Auth: header  X-API-KEY: <merchant api_key>  +  X-API-TOKEN: <merchant api_token>
 */
class PayinApiController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $merchant = $this->authenticate($request);
        if (! $merchant) {
            return response()->json(['message' => 'Invalid API credentials.'], 401);
        }

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'reference_no' => ['nullable', 'string', 'max:255'],
            'player' => ['nullable', 'string', 'max:255'],
            'callback_url' => ['nullable', 'url', 'max:1000'],
            'redirect_url' => ['nullable', 'url', 'max:1000'],
        ]);

        $payin = Payin::create([
            'user_id' => $merchant->id,
            'transaction_no' => 'PI'.now()->format('ymdHis').Str::upper(Str::random(5)),
            'amount' => $data['amount'],
            'reference_no' => $data['reference_no'] ?? null,
            'player' => $data['player'] ?? null,
            'callback_url' => $data['callback_url'] ?? null,
            'redirect_url' => $data['redirect_url'] ?? null,
            'request' => json_encode($request->all()),
            'status' => Payin::STATUS_PENDING,
            'rate' => $merchant->effectivePayinRate(),
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'transaction_no' => $payin->transaction_no,
            'status' => $payin->status,
            'amount' => $payin->amount,
        ], 201);
    }

    private function authenticate(Request $request): ?User
    {
        $key = $request->header('X-API-KEY');
        $token = $request->header('X-API-TOKEN');
        if (! $key || ! $token) {
            return null;
        }

        $merchant = User::where('role', User::ROLE_MERCHANT)
            ->where('api_key', $key)
            ->where('api_token', $token)
            ->where('is_active', true)
            ->first();

        return $merchant;
    }
}
