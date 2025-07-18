<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'order_status_id' => OrderStatus::factory(),
            'requester_name' => $this->faker->name(),
            'destination' => $this->faker->city(),
            'departure_date' => $this->faker->dateTimeBetween('now', '+1 month'),
            'return_date' => $this->faker->dateTimeBetween('+1 month', '+2 months'),
        ];
    }
}
