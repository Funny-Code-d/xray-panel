<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;

class TagController extends Controller
{
    /**
     * Список всех тегов.
     */
    public function index(): JsonResponse
    {
        $tags = Tag::query()
            ->withCount('posts')
            ->orderBy('name')
            ->get()
            ->map(fn ($tag) => [
                'id' => $tag->id,
                'code' => $tag->code,
                'name' => $tag->name,
                'description' => $tag->description,
                'color' => $tag->color,
                'posts_count' => $tag->posts_count,
            ]);

        return response()->json($tags);
    }
}