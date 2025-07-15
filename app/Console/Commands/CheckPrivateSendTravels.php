<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use App\Models\Travel;
use App\Notifications\TravelAvailableNotification;
use Carbon\Carbon;

class CheckPrivateSendTravels extends Command
{
    protected $signature = 'travels:check-private-send';
    protected $description = 'Verifica pedidos sem viagem após 24h e marca como envio próprio';

    public function handle(): int
    {
        $orders = Order::whereHas('status', function($q) {
                $q->where('name', 'approved');
            })
            ->whereDoesntHave('travel')
            ->where('created_at', '<', Carbon::now()->subDay())
            ->get();

        foreach ($orders as $order) {
            Travel::create([
                'order_id' => $order->id,
                'pickup_address' => '',
                'delivery_address' => '',
                'recipient_name' => '',
                'recipient_email' => '',
                'recipient_cpf' => '',
                'is_private_send' => true,
            ]);
            $order->user->notify(new TravelAvailableNotification($order));
        }
        return Command::SUCCESS;
    }
}
