<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\VpnClient;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Справочники
        $this->call([
            RoleSeeder::class,
            AdminUserSeeder::class,
            XrayServerSeeder::class,
            TagSeeder::class,
        ]);

        // 2. Демо-данные только локально
        if (app()->environment('local')) {
            $users = User::factory()->count(10)->create();

            // Обычным пользователям — роль 'user'
            $userRole = \App\Models\Role::where('code', 'user')->first();
            $users->each(fn ($user) => $user->roles()->attach($userRole->id));

            // Первый сервер — для демо-ключей
            $server = \App\Models\XrayServer::where('is_active', true)->first();

            // Каждому — 1-3 VPN-ключа
            $users->each(function (User $user) use ($server) {
                VpnClient::factory()
                    ->count(rand(1, 3))
                    ->for($user)
                    ->create([
                        'xray_server_id' => $server?->id,
                    ]);
            });
        }
    }
}