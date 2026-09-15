<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsNotBlocked
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->is_blocked) {
            // Удаляем все токены пользователя
            $user->tokens()->delete();

            return response()->json([
                'message' => 'Ваш аккаунт заблокирован.',
                'block_reason' => $user->block_reason,
            ], 403);
        }

        return $next($request);
    }
}
