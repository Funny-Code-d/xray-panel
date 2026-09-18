<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TagController extends Controller
{
    public function index(): JsonResponse
    {
        $tags = Tag::query()
            ->withCount(['posts', 'subscriptions'])
            ->orderBy('name')
            ->get()
            ->map(fn ($tag) => [
                'id' => $tag->id,
                'code' => $tag->code,
                'name' => $tag->name,
                'description' => $tag->description,
                'color' => $tag->color,
                'posts_count' => $tag->posts_count,
                'subscriptions_count' => $tag->subscriptions_count,
            ]);

        return response()->json($tags);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:tags,code', 'regex:/^[a-z0-9_-]+$/'],
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:255'],
            'color' => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ]);

        $tag = Tag::create($validated);

        return response()->json($tag, 201);
    }

    public function show(Tag $tag): JsonResponse
    {
        $tag->loadCount(['posts', 'subscriptions']);
        return response()->json($tag);
    }

    public function update(Request $request, Tag $tag): JsonResponse
    {
        $validated = $request->validate([
            'code' => ['sometimes', 'string', 'max:50', 'unique:tags,code,' . $tag->id, 'regex:/^[a-z0-9_-]+$/'],
            'name' => ['sometimes', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:255'],
            'color' => ['sometimes', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ]);

        $tag->update($validated);

        return response()->json($tag->fresh());
    }

    public function destroy(Tag $tag): JsonResponse
    {
        $tag->delete();
        return response()->json(['message' => 'Тег удалён']);
    }
}