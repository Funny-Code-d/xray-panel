<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'excerpt',
        'content',
        'is_published',
        'published_at',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        // Автогенерация slug при создании
        static::creating(function (Post $post) {
            if (empty($post->slug)) {
                $post->slug = static::generateUniqueSlug($post->title);
            }
        });

        // Перегенерация slug при изменении title (если slug не задан вручную)
        static::updating(function (Post $post) {
            if ($post->isDirty('title') && !$post->isDirty('slug')) {
                $post->slug = static::generateUniqueSlug($post->title, $post->id);
            }
        });
    }

    /**
     * Генерирует уникальный slug. Если такой уже есть — добавляет суффикс -1, -2, ...
     */
    protected static function generateUniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $counter = 1;

        while (static::where('slug', $slug)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = $base . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Автор поста.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Теги поста.
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    /**
     * Scope: только опубликованные посты.
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true)
            ->where('published_at', '<=', now());
    }

    /**
     * Scope: черновики.
     */
    public function scopeDraft($query)
    {
        return $query->where('is_published', false);
    }

    /**
     * Аксессор: человекочитаемый excerpt.
     * Если excerpt не задан — обрезает content.
     */
    public function getExcerptHumanAttribute(): string
    {
        if ($this->excerpt) {
            return $this->excerpt;
        }

        return Str::limit(strip_tags($this->content), 150);
    }

    /**
     * Аксессор: время чтения (в минутах).
     */
    public function getReadingTimeAttribute(): int
    {
        $words = str_word_count(strip_tags($this->content));
        return max(1, (int) ceil($words / 200)); // 200 слов в минуту
    }
}