<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\VpnClient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VpnClient>
 */
class VpnClientFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            // 'uuid' и 'email' генерируются автоматически в booted()
            'name' => fake()->randomElement([
                'iPhone', 'Ноутбук', 'Планшет', 'Десктоп', 'Android', 'MacBook'
            ]) . ' ' . fake()->firstName(),
            'is_active' => true,
            'traffic_used' => fake()->numberBetween(0, 5000000000), // до 5 ГБ
            'expires_at' => fake()->optional(0.7)->dateTimeBetween('now', '+1 year'),
            'last_connected_at' => fake()->optional(0.5)->dateTimeBetween('-1 month', 'now'),
        ];
    }

    /**
     * Ключ неактивен.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Ключ истёк.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => now()->subDay(),
        ]);
    }

    /**
     * Ключ безлимитный по времени.
     */
    public function neverExpires(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => null,
        ]);
    }
}