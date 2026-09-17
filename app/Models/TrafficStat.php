<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrafficStat extends Model
{
    use HasFactory;

    protected $fillable = [
        'vpn_client_id',
        'user_id',
        'period_start',
        'upload',
        'downlink',
        'total',
        'connections_count',
        'uptime_seconds',
    ];

    protected $casts = [
        'period_start' => 'date',
        'upload' => 'integer',
        'downlink' => 'integer',
        'total' => 'integer',
        'connections_count' => 'integer',
        'uptime_seconds' => 'integer',
    ];

    public function vpnClient(): BelongsTo
    {
        return $this->belongsTo(VpnClient::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}