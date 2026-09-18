<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostController extends Controller
{
    /**
     * Список опубликованных постов с пагинацией.
     * Поддерживает фильтр по тегу.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Post::query()
            ->published()
            ->with(['user:id,first_name,last_name', 'tags:id,code,name,color'])
            ->latest('published_at');

        // Фильтр по тегу
        if ($request->filled('tag')) {
            $tagCode = $request->input('tag');
            $query->whereHas('tags', fn ($q) => $q->where('code', $tagCode));
        }

        $posts = $query->paginate(10);

        // Преобразуем для фронта — обрезаем content, добавляем excerpt_human
        $posts->getCollection()->transform(fn ($post) => [
            'id' => $post->id,
            'title' => $post->title,
            'slug' => $post->slug,
            'excerpt' => $post->excerpt_human,
            'reading_time' => $post->reading_time,
            'published_at' => $post->published_at,
            'is_published' => $post->is_published,
            'author' => $post->user ? [
                'name' => $post->user->full_name,
            ] : null,
            'tags' => $post->tags->map(fn ($tag) => [
                'code' => $tag->code,
                'name' => $tag->name,
                'color' => $tag->color,
            ]),
        ]);

        return response()->json($posts);
    }

    /**
     * Конкретный пост по slug.
     */
    public function show(string $slug): JsonResponse
    {
        $post = Post::query()
            ->published()
            ->with(['user:id,first_name,last_name', 'tags:id,code,name,color'])
            ->where('slug', $slug)
            ->firstOrFail();

        return response()->json([
            'id' => $post->id,
            'title' => $post->title,
            'slug' => $post->slug,
            'content' => $post->content,
            'reading_time' => $post->reading_time,
            'published_at' => $post->published_at,
            'author' => $post->user ? [
                'name' => $post->user->full_name,
            ] : null,
            'tags' => $post->tags->map(fn ($tag) => [
                'code' => $tag->code,
                'name' => $tag->name,
                'color' => $tag->color,
            ]),
        ]);
    }
}