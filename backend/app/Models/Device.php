<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Device extends Model
{
    protected $fillable = [
        'device_uid', 'name', 'manufacturer', 'model', 'android_version',
        'app_version', 'api_token', 'is_active', 'last_seen_at',
    ];

    protected $hidden = ['api_token'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'last_seen_at' => 'datetime',
        ];
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    /** The app pings every 15 minutes, so 30 minutes of silence = offline. */
    public function isOnline(): bool
    {
        return $this->last_seen_at !== null && $this->last_seen_at->gt(now()->subMinutes(30));
    }
}
