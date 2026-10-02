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
        'name',
        'host',
        'api_host',
        'api_port',
        'agent_port',
        'api_token',
        'is_active',
        'last_seen_at',
        'status',
        'country',
        'country_name',
        'city',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_seen_at' => 'datetime',
        'api_port' => 'integer',
        'agent_port' => 'integer',
    ];

    protected $hidden = [
        // 'api_token',  // токен не отдаём в JSON-ответах по умолчанию
    ];

    // === Связи ===

    public function vpnClients(): HasMany
    {
        return $this->hasMany(VpnClient::class, 'xray_server_id');
    }

    public function protocols(): HasMany
    {
        return $this->hasMany(XrayServerProtocol::class, 'server_id');
    }

    public function enabledProtocols(): HasMany
    {
        return $this->protocols()->where('is_enabled', true);
    }

    // === Хелперы ===

    /**
     * Получить настройки протокола по имени.
     */
    public function protocol(string $name): ?XrayServerProtocol
    {
        return $this->protocols->firstWhere('protocol', $name);
    }

    /**
     * Включён ли протокол.
     */
    public function hasProtocol(string $name): bool
    {
        return $this->enabledProtocols->contains('protocol', $name);
    }

    /**
     * Тег основного (первого включённого) протокола.
     */
    public function primaryTag(): ?string
    {
        return $this->enabledProtocols->first()?->tag;
    }

    /**
     * Сгенерировать API-токен.
     */
    public static function generateApiToken(): string
    {
        return bin2hex(random_bytes(32));
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