<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_SUPERADMIN = 'superadmin';
    public const ROLE_ADMIN = 'admin';
    public const ROLE_STAFF = 'staff';
    public const ROLE_CUSTOMER = 'customer';
    public const ROLE_MERCHANT = 'merchant';

    public const ROLES = [
        self::ROLE_SUPERADMIN,
        self::ROLE_ADMIN,
        self::ROLE_STAFF,
        self::ROLE_CUSTOMER,
        self::ROLE_MERCHANT,
    ];

    /** Which roles each role is allowed to create. */
    public const CREATABLE = [
        self::ROLE_SUPERADMIN => [self::ROLE_ADMIN, self::ROLE_STAFF, self::ROLE_CUSTOMER],
        self::ROLE_ADMIN => [self::ROLE_STAFF, self::ROLE_CUSTOMER],
        self::ROLE_STAFF => [self::ROLE_CUSTOMER],
        self::ROLE_CUSTOMER => [self::ROLE_CUSTOMER, self::ROLE_MERCHANT],
        self::ROLE_MERCHANT => [],
    ];

    protected $fillable = [
        'name', 'username', 'email', 'password', 'role', 'parent_id', 'is_active', 'theme',
        'last_login_at', 'payin_rate', 'payout_rate',
        'payin_wallet', 'payout_wallet', 'all_wallet', 'quota',
        'api_token', 'api_key',
    ];

    protected $hidden = [
        'password', 'remember_token', 'api_token', 'api_key',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'payin_rate' => 'decimal:2',
            'payout_rate' => 'decimal:2',
            'payin_wallet' => 'decimal:2',
            'payout_wallet' => 'decimal:2',
            'all_wallet' => 'decimal:2',
            'quota' => 'decimal:2',
        ];
    }

    // -- Relationships -------------------------------------------------------

    public function parent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(User::class, 'parent_id');
    }

    public function merchants(): HasMany
    {
        return $this->hasMany(User::class, 'parent_id')->where('role', self::ROLE_MERCHANT);
    }

    public function bankSettings(): HasMany
    {
        return $this->hasMany(BankSetting::class, 'created_by');
    }

    public function payins(): HasMany
    {
        return $this->hasMany(Payin::class);
    }

    public function settlementBanks(): HasMany
    {
        return $this->hasMany(SettlementBank::class);
    }

    public function settlements(): HasMany
    {
        return $this->hasMany(Settlement::class);
    }

    // -- Role helpers --------------------------------------------------------

    public function isSuperadmin(): bool { return $this->role === self::ROLE_SUPERADMIN; }
    public function isAdmin(): bool { return $this->role === self::ROLE_ADMIN; }
    public function isStaff(): bool { return $this->role === self::ROLE_STAFF; }
    public function isCustomer(): bool { return $this->role === self::ROLE_CUSTOMER; }
    public function isMerchant(): bool { return $this->role === self::ROLE_MERCHANT; }

    /** Staff-side roles can manage the admin panel; customer/merchant are clients. */
    public function isStaffSide(): bool
    {
        return in_array($this->role, [self::ROLE_SUPERADMIN, self::ROLE_ADMIN, self::ROLE_STAFF], true);
    }

    public function hasRate(): bool
    {
        return in_array($this->role, [self::ROLE_CUSTOMER, self::ROLE_MERCHANT], true);
    }

    /** Roles this user may create. */
    public function creatableRoles(): array
    {
        return self::CREATABLE[$this->role] ?? [];
    }

    public function canCreateRole(string $role): bool
    {
        return in_array($role, $this->creatableRoles(), true);
    }

    public function roleLabel(): string
    {
        return ucfirst($this->role);
    }

    // -- Rates & hierarchy ---------------------------------------------------

    /** Rate used in the payin split. Staff-side nodes use the platform cost basis. */
    public function effectivePayinRate(): float
    {
        if ($this->hasRate() && $this->payin_rate !== null) {
            return (float) $this->payin_rate;
        }
        return (float) MasterSetting::current()->payin_cost_rate; // 100
    }

    /** All descendant user ids (children, grandchildren, …), excluding self. */
    public function descendantIds(): array
    {
        $ids = [];
        $frontier = [$this->id];
        while ($frontier) {
            $next = User::whereIn('parent_id', $frontier)->pluck('id')->all();
            $next = array_values(array_diff($next, $ids));
            if (! $next) break;
            $ids = array_merge($ids, $next);
            $frontier = $next;
        }
        return $ids;
    }

    /** Merchants somewhere under this user (direct or via sub-customers). */
    public function descendantMerchantIds(): array
    {
        $ids = array_merge([$this->id], $this->descendantIds());
        return User::whereIn('parent_id', $ids)
            ->where('role', self::ROLE_MERCHANT)
            ->pluck('id')->all();
    }
}
