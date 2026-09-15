<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsApproved
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Не аутентифицирован.'], 401);
        }

        // Админы всегда проходят
        if ($user->isAdmin()) {
            return $next($request);
        }

        if (! $user->isApproved()) {
            $message = $user->isRejected()
                ? 'Ваша заявка отклонена.'
                : 'Ваша заявка ещё не одобрена администратором.';

            return response()->json([
                'message' => $message,
                'approval_status' => $user->approval_status,
            ], 403);
        }

        return $next($request);
    }
}