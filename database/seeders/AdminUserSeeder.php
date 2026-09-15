<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@vpn.local'],
            [
                'first_name' => 'Admin',
                'last_name' => 'VPN',
                'middle_name' => null,
                'phone' => null,
                'password' => Hash::make('password'),
                'registration_date' => now(),
                'traffic_limit' => null, // безлимит
                'approval_status' => 'approved',
                'approved_at' => now(),
            ]
        );

        $adminRole = Role::where('code', 'admin')->firstOrFail();
        $admin->roles()->syncWithoutDetaching([$adminRole->id]);
    }
}