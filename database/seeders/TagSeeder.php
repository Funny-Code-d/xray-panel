<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;

class TagSeeder extends Seeder
{
    public function run(): void
    {
        $tags = [
            [
                'code' => 'updates',
                'name' => 'Обновления',
                'description' => 'Новые возможности и улучшения',
                'color' => '#3b82f6',
            ],
            [
                'code' => 'incidents',
                'name' => 'Инциденты',
                'description' => 'Проблемы и сбои в работе',
                'color' => '#ef4444',
            ],
            [
                'code' => 'maintenance',
                'name' => 'Тех. работы',
                'description' => 'Плановые работы на серверах',
                'color' => '#f59e0b',
            ],
            [
                'code' => 'news',
                'name' => 'Новости',
                'description' => 'Общие новости проекта',
                'color' => '#10b981',
            ],
        ];

        foreach ($tags as $tag) {
            Tag::updateOrCreate(['code' => $tag['code']], $tag);
        }
    }
}