<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayinLog extends Model
{
    protected $fillable = [
        'payin_id', 'user_id', 'reload_amount', 'rate', 'amount', 'date_of_record',
    ];

    protected function casts(): array
    {
        return [
            'reload_amount' => 'decimal:2',
            'rate' => 'decimal:2',
            'amount' => 'decimal:2',
            'date_of_record' => 'datetime',
        ];
    }

    public function payin(): BelongsTo
    {
        return $this->belongsTo(Payin::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
