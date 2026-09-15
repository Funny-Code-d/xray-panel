<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
    ];

    /**
     * Пользователи, у которых есть эта роль.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    /**
     * Найти роль по коду или вернёт null, если не существует.
     */
    public static function findByCode(string $code): ?self
    {
        return static::where('code', $code)->first();
    }

    /**
     * Хелпер: получить роль администратора.
     */
    public static function admin(): self
    {
        return static::where('code', 'admin')->firstOrFail();
    }

    /**
     * Хелпер: получить роль пользователя.
     */
    public static function user(): self
    {
        return static::where('code', 'user')->firstOrFail();
    }
}