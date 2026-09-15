<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class VpnClient extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'uuid',
        'email',
        'name',
        'is_active',
        'traffic_used',
        'expires_at',
        'last_connected_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'traffic_used' => 'integer',
        'expires_at' => 'datetime',
        'last_connected_at' => 'datetime',
    ];

    // Автогенерация UUID при создании
    protected static function booted(): void
    {
        static::creating(function (VpnClient $client) {
            if (empty($client->uuid)) {
                $client->uuid = (string) Str::uuid();
            }
        });
        
        static::created(function (VpnClient $client) {
        if (empty($client->email)) {
            $client->email = static::generateEmail($client->user_id, $client->id);
            $client->saveQuietly();  // без событий, чтобы не зациклиться
        }
    });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function generateEmail(int $userId, ?int $clientId = null): string
    {
        // Если clientId ещё нет (создание), используем случайный суффикс
        $suffix = $clientId ?? Str::lower(Str::random(6));
        
        return "u{$userId}_k{$suffix}@vpn.local";
    }

    // Хелперы для бизнес-логики
    
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

    /**
     * Сгенерировать vmess:// ссылку для подключения.
     */
    public function getVmessLinkAttribute(): string
    {
        $config = config('services.xray');

        $payload = [
            'v' => '2',
            'ps' => $this->name,
            'add' => $config['host'],
            'port' => (string) $config['port'],
            'id' => $this->uuid,
            'aid' => (string) $config['alter_id'],
            'scy' => 'auto',
            'net' => $config['network'],
            'type' => 'none',
            'host' => '',
            'path' => $config['path'],
            'tls' => 'none',
        ];

        // TLS добавляем только если он настроен
        if (!empty($config['tls'])) {
            $payload['tls'] = $config['tls'];
            $payload['sni'] = $config['sni'] ?: $config['host'];
        }

        $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return 'vmess://' . base64_encode($json);
    }
}