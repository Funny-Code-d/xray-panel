<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SubscriptionController extends Controller
{
    /**
     * Список подписок текущего пользователя.
     */
    public function index(Request $request): JsonResponse
    {
        $subscriptions = Subscription::query()
            ->where('user_id', $request->user()->id)
            ->with('tag:id,code,name,color')
            ->get()
            ->map(fn ($sub) => [
                'id' => $sub->id,
                'tag' => $sub->tag ? [
                    'id' => $sub->tag->id,
                    'code' => $sub->tag->code,
                    'name' => $sub->tag->name,
                    'color' => $sub->tag->color,
                ] : null,  // null = все посты
                'channel' => $sub->channel,
                'is_active' => $sub->is_active,
            ]);

        return response()->json($subscriptions);
    }

    /**
     * Создать или обновить подписку.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tag_id' => ['nullable', 'integer', 'exists:tags,id'],
            'channel' => ['required', 'string', Rule::in(['email', 'telegram'])],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $subscription = Subscription::updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'tag_id' => $validated['tag_id'] ?? null,
                'channel' => $validated['channel'],
            ],
            [
                'is_active' => $validated['is_active'] ?? true,
            ]
        );

        $subscription->load('tag:id,code,name,color');

        return response()->json([
            'id' => $subscription->id,
            'tag' => $subscription->tag ? [
                'id' => $subscription->tag->id,
                'code' => $subscription->tag->code,
                'name' => $subscription->tag->name,
                'color' => $subscription->tag->color,
            ] : null,
            'channel' => $subscription->channel,
            'is_active' => $subscription->is_active,
        ], 201);
    }

    /**
     * Удалить подписку.
     */
    public function destroy(Request $request, Subscription $subscription): JsonResponse
    {
        if ($subscription->user_id !== $request->user()->id) {
            abort(403, 'Доступ запрещён.');
        }

        $subscription->delete();

        return response()->json(['message' => 'Подписка удалена']);
    }

    /**
     * Массовое обновление подписок.
     * Принимает массив { tag_id, channel, is_active }.
     */
    public function bulkUpdate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'subscriptions' => ['present', 'array'],
            'subscriptions.*.tag_id' => ['nullable', 'integer', 'exists:tags,id'],
            'subscriptions.*.channel' => ['required', Rule::in(['email', 'telegram'])],
            'subscriptions.*.is_active' => ['required', 'boolean'],
        ]);

        $user = $request->user();

        // Удаляем все текущие подписки пользователя
        Subscription::where('user_id', $user->id)->delete();

        // Создаём новые
        $created = collect($validated['subscriptions'])
            ->filter(fn ($sub) => $sub['is_active'])
            ->map(fn ($sub) => Subscription::create([
                'user_id' => $user->id,
                'tag_id' => $sub['tag_id'] ?? null,
                'channel' => $sub['channel'],
                'is_active' => true,
            ]));

        return response()->json([
            'message' => 'Подписки обновлены',
            'count' => $created->count(),
        ]);
    }
}