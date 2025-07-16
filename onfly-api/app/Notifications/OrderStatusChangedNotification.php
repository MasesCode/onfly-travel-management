<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class OrderStatusChangedNotification extends Notification
{
    use Queueable;

    public function __construct(public Order $order) {}

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Status do pedido de viagem atualizado')
            ->greeting('Olá, ' . $notifiable->name)
            ->line('O status do seu pedido de viagem foi alterado para: ' . ucfirst($this->order->status->name))
            ->line('Destino: ' . $this->order->destination)
            ->line('Data de ida: ' . $this->order->departure_date)
            ->line('Data de volta: ' . $this->order->return_date)
            ->action('Ver pedido', url('/'))
            ->line('Se você não reconhece esta alteração, entre em contato com o suporte.');
    }

    public function toArray($notifiable): array
    {
        return [
            'order_id' => $this->order->id,
            'status' => $this->order->status->name,
            'destination' => $this->order->destination,
            'departure_date' => $this->order->departure_date,
            'return_date' => $this->order->return_date,
        ];
    }
}
