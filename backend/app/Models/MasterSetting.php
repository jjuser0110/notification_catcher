<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Single-row table holding the platform-wide rate bands.
 *
 *  Payin : cost 100% (platform basis)  >  default 98%  >  max 95%   -> band 95..98
 *  Payout: cost 0%   (platform basis)  <  default 2%   <  max 5%    -> band 2..5
 */
class MasterSetting extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'payin_cost_rate' => 'decimal:2',
            'payin_default_rate' => 'decimal:2',
            'payin_max_rate' => 'decimal:2',
            'payout_cost_rate' => 'decimal:2',
            'payout_default_rate' => 'decimal:2',
            'payout_max_rate' => 'decimal:2',
        ];
    }

    /** The one settings row, created with defaults if missing. */
    public static function current(): self
    {
        return static::first() ?? static::create([]);
    }

    /** Inclusive [low, high] band a payin rate must fall within. */
    public function payinBand(): array
    {
        return [
            (float) min($this->payin_default_rate, $this->payin_max_rate),
            (float) max($this->payin_default_rate, $this->payin_max_rate),
        ];
    }

    public function payoutBand(): array
    {
        return [
            (float) min($this->payout_default_rate, $this->payout_max_rate),
            (float) max($this->payout_default_rate, $this->payout_max_rate),
        ];
    }
}
