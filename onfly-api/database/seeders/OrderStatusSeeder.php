<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\OrderStatus;

class OrderStatusSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            ['name' => 'requested', 'is_custom' => false],
            ['name' => 'approved', 'is_custom' => false],
            ['name' => 'cancelled', 'is_custom' => false],
        ];
        foreach ($statuses as $status) {
            OrderStatus::firstOrCreate(['name' => $status['name']], $status);
        }
    }
}
