<?php

namespace App\Services;

use App\Models\Payin;
use App\Models\PayinLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Settles a successful payin: credits the merchant its funds and pays every
 * ancestor the margin between their rate and the node just below them, writing
 * one payin_log row per beneficiary and bumping their payin_wallet.
 *
 * The amounts telescope, so the total credited across everyone equals the
 * reload amount times the platform cost basis (100%), i.e. the full amount.
 *
 *   merchant          R * merchant_rate%
 *   customer (parent) R * (customer_rate - merchant_rate)%
 *   …up the chain…
 *   top staff node    R * (100% - top_customer_rate)%
 */
class PayinService
{
    /** Idempotent: does nothing if this payin was already split. */
    public static function distribute(Payin $payin): void
    {
        if ($payin->logs()->exists()) {
            return;
        }

        $reload = (float) $payin->amount;
        $when = $payin->approved_date ?? now();

        // Build the chain from the merchant up to the root.
        $chain = [];
        $node = $payin->merchant;
        $guard = 0;
        while ($node && $guard++ < 50) {
            $chain[] = $node;
            $node = $node->parent;
        }

        DB::transaction(function () use ($chain, $reload, $payin, $when) {
            $childRate = null; // rate of the node below the current one
            foreach ($chain as $i => $user) {
                $rate = $user->effectivePayinRate();

                $earning = $i === 0
                    ? $reload * $rate / 100                       // merchant's own funds
                    : $reload * ($rate - $childRate) / 100;       // commission margin

                $earning = round($earning, 2);
                $childRate = $rate;

                if ($earning == 0.0 && $i !== 0) {
                    continue; // skip zero-margin intermediaries (e.g. staff above staff)
                }

                PayinLog::create([
                    'payin_id' => $payin->id,
                    'user_id' => $user->id,
                    'reload_amount' => $reload,
                    'rate' => $rate,
                    'amount' => $earning,
                    'date_of_record' => $when,
                ]);

                $user->forceFill([
                    'payin_wallet' => (float) $user->payin_wallet + $earning,
                    'all_wallet' => (float) $user->all_wallet + $earning,
                ])->save();
            }
        });
    }
}
