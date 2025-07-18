<?php

namespace Database\Factories;

use App\Models\Travel;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

class TravelFactory extends Factory
{
    protected $model = Travel::class;

    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'pickup_address' => $this->faker->address(),
            'delivery_address' => $this->faker->address(),
            'recipient_name' => $this->faker->name(),
            'recipient_email' => $this->faker->email(),
            'recipient_cpf' => $this->faker->numerify('###########'),
            'weight' => $this->faker->numberBetween(1, 50),
            'height' => $this->faker->numberBetween(10, 100),
            'width' => $this->faker->numberBetween(10, 100),
            'length' => $this->faker->numberBetween(10, 100),
            'is_private_send' => $this->faker->boolean(),
        ];
    }
}
