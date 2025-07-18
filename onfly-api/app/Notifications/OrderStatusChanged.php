<?php

namespace App\Notifications;

use App\Models\Order;
use App\Models\OrderStatus;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderStatusChanged extends Notification
{
    use Queueable;

    protected $order;
    protected $oldStatusId;
    protected $newStatusId;

    /**
     * Create a new notification instance.
     */
    public function __construct(Order $order, $oldStatusId, $newStatusId)
    {
        $this->order = $order;
        $this->oldStatusId = $oldStatusId;
        $this->newStatusId = $newStatusId;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        // Buscar os nomes dos status
        $newStatus = OrderStatus::find($this->newStatusId);
        $oldStatus = OrderStatus::find($this->oldStatusId);

        $newStatusName = $newStatus ? $newStatus->name : 'desconhecido';
        $oldStatusName = $oldStatus ? $oldStatus->name : 'desconhecido';

        $title = '';
        $message = '';
        $type = '';

        switch ($newStatusName) {
            case 'approved':
                $title = 'Pedido Aprovado!';
                $message = "Seu pedido para {$this->order->destination} foi aprovado.";
                $type = 'approved';
                break;
            case 'cancelled':
                $title = 'Pedido Cancelado';
                $message = "Seu pedido para {$this->order->destination} foi cancelado.";
                $type = 'cancelled';
                break;
            case 'requested':
                $title = 'Pedido em Análise';
                $message = "Seu pedido para {$this->order->destination} está em análise.";
                $type = 'pending';
                break;
            default:
                $title = 'Status do Pedido Atualizado';
                $message = "O status do seu pedido para {$this->order->destination} foi alterado para {$newStatusName}.";
                $type = 'info';
                break;
        }

        return [
            'order_id' => $this->order->id,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'old_status' => $oldStatusName,
            'new_status' => $newStatusName,
            'destination' => $this->order->destination,
        ];
    }
}
