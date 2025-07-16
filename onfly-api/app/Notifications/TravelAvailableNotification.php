<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class TravelAvailableNotification extends Notification
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
            ->subject('Viagem disponível para seu pedido')
            ->greeting('Olá, ' . $notifiable->name)
            ->line('Seu pedido de viagem está pronto para agendamento de retirada e entrega.')
            ->action('Agendar viagem', url('/'))
            ->line('Se não for agendar, o envio será feito automaticamente após 24h.');
    }

    public function toArray($notifiable): array
    {
        return [
            'order_id' => $this->order->id,
            'message' => 'Viagem disponível para seu pedido.',
        ];
    }
}
