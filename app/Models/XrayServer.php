<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class XrayServer extends Model
{
    use HasFactory;

    protected $appends = ['country_flag'];

    protected $fillable = [
        'name', 'host', 'port',
        'api_host', 'api_port',
        'protocol', 'inbound_tag',
        'network', 'security', 'flow',
        'reality_dest', 'reality_server_names', 'reality_private_key',
        'reality_public_key', 'reality_short_ids', 'fingerprint',
        'ws_path',
        'api_token', 'is_active', 'last_seen_at', 'status',
        'alter_id',
        'agent_port',
        'country',
        'country_name',
        'city',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_seen_at' => 'datetime',
        'port' => 'integer',
        'api_port' => 'integer',
        'reality_server_names' => 'array',
        'reality_short_ids' => 'array',
        'alter_id' => "integer",
        'agent_port' => 'integer',
    ];

    protected $hidden = [
        'reality_private_key',  // приватный ключ никогда не отдаём в JSON
    ];

    public function vpnClients(): HasMany
    {
        return $this->hasMany(VpnClient::class);
    }

    /**
     * Сгенерировать API-токен.
     */
    public static function generateApiToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    /**
     * Первый shortId (для ссылок).
     */
    public function getPrimaryShortIdAttribute(): ?string
    {
        return $this->reality_short_ids[0] ?? null;
    }

    /**
     * Первый serverName (для sni).
     */
    public function getPrimaryServerNameAttribute(): ?string
    {
        return $this->reality_server_names[0] ?? null;
    }

    /**
     * Сгенерировать vless:// ссылку для конкретного UUID.
     */
    public function buildVlessLink(string $uuid, string $name): string
    {
        $params = [
            'type' => $this->network,
            'security' => $this->security,
        ];

        if ($this->security === 'reality') {
            $params['flow'] = $this->flow ?: 'xtls-rprx-vision';
            $params['sni'] = $this->primary_server_name;
            $params['fp'] = $this->fingerprint;
            $params['pbk'] = $this->reality_public_key;
            $params['sid'] = $this->primary_short_id;
            $params['spx'] = '/';
        }

        if ($this->security === 'tls') {
            $params['sni'] = $this->primary_server_name;
            $params['fp'] = $this->fingerprint;
        }

        if ($this->network === 'ws') {
            $params['path'] = $this->ws_path;
            $params['host'] = $this->host;
        }

        $query = http_build_query($params, '', '&', PHP_QUERY_RFC3986);
        $fragment = rawurlencode($name);

        return "vless://{$uuid}@{$this->host}:{$this->port}?{$query}#{$fragment}";
    }

    /**
     * Эмодзи-флаг из ISO-кода страны (DE → 🇩🇪).
     */
    public function getCountryFlagAttribute(): string
    {
        if (!$this->country || strlen($this->country) !== 2) {
            return '🌐';
        }

        $code = strtoupper($this->country);
        $flag = '';

        foreach (str_split($code) as $char) {
            $flag .= mb_chr(ord($char) + 127397, 'UTF-8');
        }

        return $flag;
    }
}