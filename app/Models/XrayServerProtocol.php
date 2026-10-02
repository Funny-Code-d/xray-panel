<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class XrayServerProtocol extends Model
{
    use HasFactory;

    protected $fillable = [
        'server_id',
        'protocol',
        'is_enabled',
        'port',
        'tag',
        'settings',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'port' => 'integer',
        'settings' => 'array',
    ];

    public function server(): BelongsTo
    {
        return $this->belongsTo(XrayServer::class, 'server_id');
    }

    public function getSetting(string $key, mixed $default = null): mixed
    {
        return data_get($this->settings, $key, $default);
    }

    public static function availableProtocols(): array
    {
        return [
            'vless' => 'VLESS + Reality',
            'vmess' => 'VMess + WS + TLS',
            'trojan' => 'Trojan + TLS',
        ];
    }
}