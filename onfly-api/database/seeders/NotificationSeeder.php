<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Notification;
use App\Models\User;
use App\Notifications\OrderStatusChanged;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();

        foreach ($users as $user) {
            // Criar algumas notificações para cada usuário usando a estrutura correta
            for ($i = 1; $i <= 3; $i++) {
                // Simular um pedido de viagem
                $orderId = rand(1, 100);
                $oldStatus = 'pending';
                $newStatus = $i % 2 == 0 ? 'approved' : 'cancelled';

                // Criar uma instância fake de order
                $orderData = (object)[
                    'id' => $orderId,
                    'destination' => ['São Paulo', 'Rio de Janeiro', 'Brasília', 'Salvador'][rand(0, 3)],
                ];

                // Criar notificação usando notificação direta no banco
                $user->notifications()->create([
                    'id' => \Illuminate\Support\Str::uuid(),
                    'type' => OrderStatusChanged::class,
                    'data' => [
                        'order_id' => $orderId,
                        'title' => $newStatus === 'approved' ? 'Pedido Aprovado!' : 'Pedido Cancelado',
                        'message' => $newStatus === 'approved'
                            ? "Seu pedido para {$orderData->destination} foi aprovado."
                            : "Seu pedido para {$orderData->destination} foi cancelado.",
                        'type' => $newStatus,
                        'old_status' => $oldStatus,
                        'new_status' => $newStatus,
                        'destination' => $orderData->destination,
                    ],
                    'read_at' => null,
                    'created_at' => now()->subDays(rand(0, 7))->subHours(rand(0, 23)),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
