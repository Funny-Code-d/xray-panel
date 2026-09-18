<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostController extends Controller
{
    /**
     * Список всех постов (включая черновики).
     */
    public function index(Request $request): JsonResponse
    {
        $query = Post::query()
            ->with(['user:id,first_name,last_name', 'tags:id,code,name,color']);

        // Фильтр по статусу
        if ($request->filled('status')) {
            $query->where('is_published', $request->input('status') === 'published');
        }

        // Поиск по заголовку
        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->input('search') . '%');
        }

        return response()->json($query->latest()->paginate(20));
    }

    /**
     * Создать пост.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'content' => ['required', 'string'],
            'is_published' => ['sometimes', 'boolean'],
            'published_at' => ['nullable', 'date'],
            'tags' => ['sometimes', 'array'],
            'tags.*' => ['integer', 'exists:tags,id'],
        ]);

        $post = Post::create([
            'user_id' => $request->user()->id,
            'title' => $validated['title'],
            'excerpt' => $validated['excerpt'] ?? null,
            'content' => $validated['content'],
            'is_published' => $validated['is_published'] ?? false,
            'published_at' => ($validated['is_published'] ?? false) 
                ? ($validated['published_at'] ?? now()) 
                : null,
        ]);

        // Привязываем теги
        if (isset($validated['tags'])) {
            $post->tags()->sync($validated['tags']);
        }

        $post->load(['user:id,first_name,last_name', 'tags:id,code,name,color']);

        return response()->json($post, 201);
    }

    /**
     * Конкретный пост.
     */
    public function show(Post $post): JsonResponse
    {
        $post->load(['user:id,first_name,last_name', 'tags:id,code,name,color']);
        return response()->json($post);
    }

    /**
     * Обновить пост.
     */
    public function update(Request $request, Post $post): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'slug' => ['sometimes', 'string', 'max:255', 'unique:posts,slug,' . $post->id],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'content' => ['sometimes', 'string'],
            'is_published' => ['sometimes', 'boolean'],
            'published_at' => ['nullable', 'date'],
            'tags' => ['sometimes', 'array'],
            'tags.*' => ['integer', 'exists:tags,id'],
        ]);

        // Если публикуем впервые и published_at не задан — ставим сейчас
        if (isset($validated['is_published']) && $validated['is_published'] && !$post->published_at) {
            $validated['published_at'] = now();
        }

        $post->update($validated);

        // Синхронизируем теги
        if (isset($validated['tags'])) {
            $post->tags()->sync($validated['tags']);
        }

        return response()->json($post->fresh(['user:id,first_name,last_name', 'tags:id,code,name,color']));
    }

    /**
     * Удалить пост.
     */
    public function destroy(Post $post): JsonResponse
    {
        $post->delete();
        return response()->json(['message' => 'Пост удалён']);
    }
}