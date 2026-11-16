<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payin extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_SUCCESS = 'success';
    public const STATUS_FAILED = 'failed';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'user_id', 'bank_setting_id', 'transaction_no', 'amount', 'reference_no',
        'player', 'callback_url', 'redirect_url', 'request', 'response', 'status',
        'rate', 'nett_amount', 'approved_date', 'callback_response', 'notify_status',
        'callback_attempt', 'ip_address', 'manual_update_by',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'rate' => 'decimal:2',
            'nett_amount' => 'decimal:2',
            'approved_date' => 'datetime',
            'callback_attempt' => 'integer',
        ];
    }

    public function merchant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function bankSetting(): BelongsTo
    {
        return $this->belongsTo(BankSetting::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(PayinLog::class);
    }

    public function manualUpdateBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manual_update_by');
    }

    public function isSuccess(): bool
    {
        return $this->status === self::STATUS_SUCCESS;
    }
}
