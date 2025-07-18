<?php

namespace Database\Factories;

use App\Models\OrderStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderStatusFactory extends Factory
{
    protected $model = OrderStatus::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement(['requested', 'approved', 'cancelled']),
            'is_custom' => false,
        ];
    }

    public function requested(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'requested',
        ]);
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'approved',
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'cancelled',
        ]);
    }

    public function custom(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => $this->faker->word(),
            'is_custom' => true,
        ]);
    }
}
