<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Order;
use App\Models\OrderStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statusRequested = OrderStatus::where('name', 'requested')->first();
        $statusApproved = OrderStatus::where('name', 'approved')->first();
        $statusCancelled = OrderStatus::where('name', 'cancelled')->first();

        if (!$statusRequested) {
            $statusRequested = OrderStatus::create(['name' => 'requested', 'is_custom' => false]);
        }
        if (!$statusApproved) {
            $statusApproved = OrderStatus::create(['name' => 'approved', 'is_custom' => false]);
        }
        if (!$statusCancelled) {
            $statusCancelled = OrderStatus::create(['name' => 'cancelled', 'is_custom' => false]);
        }

        $user1 = User::firstOrCreate(
            ['email' => 'joao@example.com'],
            [
                'name' => 'João Silva',
                'password' => Hash::make('password'),
                'is_admin' => false,
            ]
        );

        $user2 = User::firstOrCreate(
            ['email' => 'maria@example.com'],
            [
                'name' => 'Maria Santos',
                'password' => Hash::make('password'),
                'is_admin' => false,
            ]
        );

        $user3 = User::firstOrCreate(
            ['email' => 'carlos@example.com'],
            [
                'name' => 'Carlos Oliveira',
                'password' => Hash::make('password'),
                'is_admin' => false,
            ]
        );

        Order::truncate();

        Order::create([
            'user_id' => $user1->id,
            'order_status_id' => $statusRequested->id,
            'requester_name' => $user1->name,
            'destination' => 'São Paulo, SP',
            'departure_date' => '2025-08-15',
            'return_date' => '2025-08-18',
        ]);

        Order::create([
            'user_id' => $user1->id,
            'order_status_id' => $statusApproved->id,
            'requester_name' => $user1->name,
            'destination' => 'Rio de Janeiro, RJ',
            'departure_date' => '2025-07-10',
            'return_date' => '2025-07-12',
        ]);

        Order::create([
            'user_id' => $user2->id,
            'order_status_id' => $statusRequested->id,
            'requester_name' => $user2->name,
            'destination' => 'Belo Horizonte, MG',
            'departure_date' => '2025-08-20',
            'return_date' => '2025-08-22',
        ]);

        Order::create([
            'user_id' => $user2->id,
            'order_status_id' => $statusApproved->id,
            'requester_name' => $user2->name,
            'destination' => 'Salvador, BA',
            'departure_date' => '2025-09-05',
            'return_date' => '2025-09-08',
        ]);

        Order::create([
            'user_id' => $user3->id,
            'order_status_id' => $statusCancelled->id,
            'requester_name' => $user3->name,
            'destination' => 'Porto Alegre, RS',
            'departure_date' => '2025-06-25',
            'return_date' => '2025-06-27',
        ]);

        Order::create([
            'user_id' => $user3->id,
            'order_status_id' => $statusRequested->id,
            'requester_name' => $user3->name,
            'destination' => 'Brasília, DF',
            'departure_date' => '2025-08-15',
            'return_date' => '2025-08-17',
        ]);

        Order::create([
            'user_id' => $user1->id,
            'order_status_id' => $statusApproved->id,
            'requester_name' => $user1->name,
            'destination' => 'Recife, PE',
            'departure_date' => '2025-09-10',
            'return_date' => '2025-09-13',
        ]);

        Order::create([
            'user_id' => $user2->id,
            'order_status_id' => $statusRequested->id,
            'requester_name' => $user2->name,
            'destination' => 'Curitiba, PR',
            'departure_date' => '2025-07-28',
            'return_date' => '2025-08-02',
        ]);
    }
}
