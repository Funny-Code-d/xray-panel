<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'code' => 'admin',
                'name' => 'Администратор',
                'description' => 'Полный доступ к системе',
            ],
            [
                'code' => 'user',
                'name' => 'Пользователь',
                'description' => 'Обычный пользователь VPN',
            ],
            [
                'code' => 'moder',
                'name' => 'Модератор',
                'description' => 'Модератор',
            ],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(
                ['code' => $role['code']],
                $role
            );
        }
    }
}