<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\VpnClient;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'users' => [
                'total' => User::count(),
                'pending' => User::where('approval_status', 'pending')->count(),
                'approved' => User::where('approval_status', 'approved')->count(),
                'rejected' => User::where('approval_status', 'rejected')->count(),
                'blocked' => User::where('is_blocked', true)->count(),
                'new_this_week' => User::where('created_at', '>=', now()->subWeek())->count(),
            ],
            'clients' => [
                'total' => VpnClient::count(),
                'active' => VpnClient::where('is_active', true)->count(),
                'inactive' => VpnClient::where('is_active', false)->count(),
                'new_this_week' => VpnClient::where('created_at', '>=', now()->subWeek())->count(),
            ],
            'traffic' => [
                'total_used' => (int) VpnClient::sum('traffic_used'),
            ],
            'recent_pending' => User::where('approval_status', 'pending')
                ->with('roles:id,code,name')
                ->latest()
                ->limit(5)
                ->get()
                ->map(fn ($u) => [
                    'id' => $u->id,
                    'full_name' => $u->full_name,
                    'email' => $u->email,
                    'registration_date' => $u->registration_date,
                ]),
            'recent_clients' => VpnClient::with('user:id,first_name,last_name,email')
                ->latest()
                ->limit(5)
                ->get()
                ->map(fn ($c) => [
                    'id' => $c->id,
                    'name' => $c->name,
                    'email' => $c->email,
                    'user_name' => $c->user?->full_name,
                    'is_active' => $c->is_active,
                    'created_at' => $c->created_at,
                ]),
        ]);
    }
}