<?php

namespace App\Services;

use App\Models\MasterSetting;
use App\Models\User;

/**
 * Works out the payin/payout rate range a creator may assign to a new
 * customer or merchant, enforcing two things at once:
 *
 *   1. the platform master band (payin 95..98, payout 2..5), and
 *   2. that the creator keeps a non-negative margin — a child can never be
 *      given a "better" rate than its parent.
 *
 * Payin:  higher rate = more credited to the child, less margin to the parent,
 *         so the child's rate may not exceed the parent's rate.
 * Payout: higher rate = bigger fee the child pays, more margin to the parent,
 *         so the child's rate may not be lower than the parent's rate.
 */
class RateService
{
    /** @return array{0: float, 1: float} inclusive [min, max] for payin. */
    public static function payinRange(User $creator): array
    {
        $settings = MasterSetting::current();
        [$bandLow, $bandHigh] = $settings->payinBand(); // [95, 98]

        // If the creator itself has a payin rate, that caps the child's upper bound.
        if ($creator->hasRate() && $creator->payin_rate !== null) {
            $bandHigh = min($bandHigh, (float) $creator->payin_rate);
        }
        return [$bandLow, $bandHigh];
    }

    /** @return array{0: float, 1: float} inclusive [min, max] for payout. */
    public static function payoutRange(User $creator): array
    {
        $settings = MasterSetting::current();
        [$bandLow, $bandHigh] = $settings->payoutBand(); // [2, 5]

        // The creator's own payout rate raises the child's lower bound.
        if ($creator->hasRate() && $creator->payout_rate !== null) {
            $bandLow = max($bandLow, (float) $creator->payout_rate);
        }
        return [$bandLow, $bandHigh];
    }

    public static function payinDefault(User $creator): float
    {
        // Merchant follows its customer's rate; otherwise the band's top.
        if ($creator->hasRate() && $creator->payin_rate !== null) {
            return (float) $creator->payin_rate;
        }
        return (float) MasterSetting::current()->payin_default_rate;
    }

    public static function payoutDefault(User $creator): float
    {
        if ($creator->hasRate() && $creator->payout_rate !== null) {
            return (float) $creator->payout_rate;
        }
        return (float) MasterSetting::current()->payout_default_rate;
    }
}
