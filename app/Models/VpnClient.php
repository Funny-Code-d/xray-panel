<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class VpnClient extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'xray_server_id',
        'uuid',
        'email',
        'name',
        'is_active',
        'traffic_used',
        'expires_at',
        'last_connected_at',
        'xray_last_upload',
        'xray_last_downlink',
        'last_synced_at',
        'trojan_password',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'traffic_used' => 'integer',
        'expires_at' => 'datetime',
        'last_connected_at' => 'datetime',
        'xray_last_upload' => 'integer',
        'xray_last_downlink' => 'integer',
        'last_synced_at' => 'datetime',
    ];

    // Автогенерация UUID и email при создании
    protected static function booted(): void
    {
        static::creating(function (VpnClient $client) {
            if (empty($client->uuid)) {
                $client->uuid = (string) Str::uuid();
            }
            if (empty($client->trojan_password)) {
                $client->trojan_password = static::generateTrojanPassword();
            }
        });

        static::created(function (VpnClient $client) {
            if (empty($client->email)) {
                $client->email = static::generateEmail($client->user_id, $client->id);
                $client->saveQuietly();
            }
        });
    }

    // === Связи ===

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function xrayServer(): BelongsTo
    {
        return $this->belongsTo(XrayServer::class, 'xray_server_id');
    }

    public function server(): BelongsTo
    {
        return $this->belongsTo(XrayServer::class, 'xray_server_id');
    }

    public function trafficStats(): HasMany
    {
        return $this->hasMany(TrafficStat::class);
    }

    // === Хелперы ===

    public static function generateEmail(int $userId, ?int $clientId = null): string
    {
        $suffix = $clientId ?? Str::lower(Str::random(6));

        return "u{$userId}_k{$suffix}@vpn.local";
    }

    public static function generateTrojanPassword(int $length = 32): string
    {
        return bin2hex(random_bytes($length / 2));
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function isUsable(): bool
    {
        return $this->is_active
            && !$this->isExpired()
            && !$this->isTrafficExceeded();
    }

    public function getTrafficUsedHumanAttribute(): string
    {
        return $this->formatBytes($this->traffic_used);
    }
}