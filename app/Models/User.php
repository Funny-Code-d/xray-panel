<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

use App\Support\TrafficFormatter;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'first_name',
        'last_name',
        'middle_name',
        'phone',
        'email',
        'password',
        'registration_date',
        'last_auth_date',
        'traffic_limit',
        'approval_status',
        'approved_at',
        'approved_by',
        'rejection_reason',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'registration_date' => 'datetime',
        'last_auth_date' => 'datetime',
        'traffic_limit' => "integer",
        'password' => 'hashed',
        'approved_at' => 'datetime',
    ];

    /**
     * Роли пользователя (many-to-many).
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class)->withTimestamps();
    }

    /**
     * VPN-ключи пользователя (one-to-many).
     */
    public function vpnClients(): HasMany
    {
        return $this->hasMany(VpnClient::class);
    }

    /**
     * Суммарный использованный трафик по всем ключам.
     */
    public function getTotalTrafficUsedAttribute(): int
    {
        return (int) $this->vpnClients()->sum('traffic_used');
    }

    /**
     * Превышен ли лимит трафика.
     */
    public function isTrafficExceeded(): bool
    {
        return $this->traffic_limit !== null
            && $this->total_traffic_used >= $this->traffic_limit;
    }

    /**
     * Остаток трафика в байтах.
     * null — если безлимит.
     */
    public function getTrafficRemainingAttribute(): ?int
    {
        if ($this->traffic_limit === null) {
            return null;
        }
        return max(0, $this->traffic_limit - $this->total_traffic_used);
    }

    /**
     * Человекочитаемые значения.
     */
    public function getTrafficLimitHumanAttribute(): string
    {
        return $this->traffic_limit
            ? TrafficFormatter::format($this->traffic_limit)
            : 'Безлимит';
    }

    public function getTotalTrafficUsedHumanAttribute(): string
    {
        return TrafficFormatter::format($this->total_traffic_used);
    }

    public function getTrafficRemainingHumanAttribute(): string
    {
        return $this->traffic_remaining === null
            ? '∞'
            : TrafficFormatter::format($this->traffic_remaining);
    }

    /**
     * Есть ли у пользователя конкретная роль.
     */
    public function hasRole(string $code): bool
    {
        return $this->roles->contains('code', $code);
    }

    /**
     * Есть ли у пользователя хотя бы одна из ролей.
     */
    public function hasAnyRole(array $codes): bool
    {
        return $this->roles->whereIn('code', $codes)->isNotEmpty();
    }

    /**
     * Является ли пользователь администратором.
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    /**
     * Аксессор: полное имя (Фамилия Имя Отчество).
     */
    public function getFullNameAttribute(): string
    {
        return trim("{$this->last_name} {$this->first_name} {$this->middle_name}");
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function isApproved(): bool
    {
        return $this->approval_status === 'approved';
    }

    public function isPending(): bool
    {
        return $this->approval_status === 'pending';
    }

    public function isRejected(): bool
    {
        return $this->approval_status === 'rejected';
    }
}