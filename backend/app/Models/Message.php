<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    protected $fillable = [
        'device_id', 'type', 'app_package', 'app_name', 'sender',
        'title', 'body', 'received_at', 'hash',
    ];

    protected function casts(): array
    {
        return ['received_at' => 'datetime'];
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }
}
