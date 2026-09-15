<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\Role;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Список пользователей с фильтрами и пагинацией.
     */
    public function index(Request $request): JsonResponse
    {
        $query = User::query()
            ->with('roles:id,code,name')
            ->withCount('vpnClients')
            ->withSum('vpnClients as total_traffic_used', 'traffic_used');

        // Фильтр по статусу одобрения
        if ($request->filled('status')) {
            $query->where('approval_status', $request->input('status'));
        }

        // Поиск по имени/email
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $users = $query->latest()->paginate(20);

        return response()->json($users);
    }

    /**
     * Карточка пользователя со всеми данными.
     */
    public function show(User $user): JsonResponse
    {
        // Загружаем связи: роли пользователя и его VPN-ключи
        $user->load([
            'roles:id,code,name',
            'vpnClients' => function ($q) {
                $q->latest();
            },
        ]);

        return response()->json([
            // === Персональные данные ===
            'id' => $user->id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'middle_name' => $user->middle_name,
            'full_name' => $user->full_name,
            'email' => $user->email,
            'phone' => $user->phone,

            // === Роли пользователя ===
            'roles' => $user->roles->map(fn ($r) => [
                'code' => $r->code,
                'name' => $r->name,
            ]),

            // === ВСЕ доступные роли в системе (для чекбоксов) ===
            'all_roles' => \App\Models\Role::select('code', 'name')->get(),

            // === Права и статус ===
            'is_admin' => $user->isAdmin(),
            'approval_status' => $user->approval_status,
            'approved_at' => $user->approved_at,
            'rejection_reason' => $user->rejection_reason,

            // === Блокировка ===
            'is_blocked' => $user->is_blocked,
            'blocked_at' => $user->blocked_at,
            'block_reason' => $user->block_reason,

            // === Трафик ===
            'traffic_limit' => $user->traffic_limit,
            'traffic_used' => $user->total_traffic_used,

            // === Метаданные ===
            'registration_date' => $user->registration_date,
            'last_auth_date' => $user->last_auth_date,

            // === VPN-ключи ===
            'vpn_clients_count' => $user->vpnClients->count(),
            'vpn_clients' => $user->vpnClients->map(fn ($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'email' => $c->email,
                'is_active' => $c->is_active,
                'traffic_used' => $c->traffic_used,
                'expires_at' => $c->expires_at,
                'created_at' => $c->created_at,
            ]),
        ]);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'traffic_limit' => ['nullable', 'integer', 'min:0'],
            'roles' => ['sometimes', 'array'],
            'roles.*' => ['string', 'exists:roles,code'],
        ]);

        // Защита: админ не может снять с себя роль admin
        if ($request->has('roles') && $user->id === $request->user()->id) {
            if (!in_array('admin', $validated['roles'] ?? [])) {
                return response()->json([
                    'message' => 'Вы не можете снять с себя роль администратора.',
                ], 403);
            }
        }

        // Обновляем лимит трафика
        if ($request->has('traffic_limit')) {
            $user->traffic_limit = $validated['traffic_limit'] ?? null;
            $user->save();
        }

        // Обновляем роли
        if ($request->has('roles')) {
            $roleIds = Role::whereIn('code', $validated['roles'])->pluck('id');
            $user->roles()->sync($roleIds);
        }

        $user->load('roles:id,code,name');

        return response()->json([
            'id' => $user->id,
            'traffic_limit' => $user->traffic_limit,
            'roles' => $user->roles->map(fn ($r) => [
                'code' => $r->code,
                'name' => $r->name,
            ]),
        ]);
    }

    /**
     * Одобрить заявку.
     */
    public function approve(Request $request, User $user): JsonResponse
    {
        $user->update([
            'approval_status' => 'approved',
            'approved_at' => now(),
            'approved_by' => $request->user()->id,
            'rejection_reason' => null,
        ]);

        return response()->json($user->fresh('roles'));
    }

    /**
     * Отклонить заявку.
     */
    public function reject(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $user->update([
            'approval_status' => 'rejected',
            'approved_at' => null,
            'approved_by' => $request->user()->id,
            'rejection_reason' => $validated['reason'] ?? null,
        ]);

        return response()->json($user->fresh('roles'));
    }
    
    public function block(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        // Защита: админ не может заблокировать себя
        if ($user->id === $request->user()->id) {
            return response()->json([
                'message' => 'Вы не можете заблокировать себя.',
            ], 403);
        }

        // Защита: нельзя заблокировать другого админа
        if ($user->isAdmin()) {
            return response()->json([
                'message' => 'Нельзя заблокировать администратора. Сначала снимите роль.',
            ], 403);
        }

        $user->update([
            'is_blocked' => true,
            'blocked_at' => now(),
            'block_reason' => $validated['reason'] ?? null,
            'blocked_by' => $request->user()->id,
        ]);

        // Деактивируем все VPN-ключи
        $user->vpnClients()->update(['is_active' => false]);

        // Отзываем все токены — пользователь вылетает из системы
        $user->tokens()->delete();

        return response()->json([
            'id' => $user->id,
            'is_blocked' => true,
            'blocked_at' => $user->blocked_at,
            'block_reason' => $user->block_reason,
            'vpn_clients_deactivated' => $user->vpnClients()->count(),
        ]);
    }

    public function unblock(Request $request, User $user): JsonResponse
    {
        if (! $user->isBlocked()) {
            return response()->json([
                'message' => 'Пользователь не заблокирован.',
            ], 422);
        }

        $user->update([
            'is_blocked' => false,
            'blocked_at' => null,
            'block_reason' => null,
            'blocked_by' => null,
        ]);

        $user->vpnClients()->update(['is_active' => true]);

        return response()->json([
            'id' => $user->id,
            'is_blocked' => false,
        ]);
    }
}