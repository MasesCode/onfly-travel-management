<?php

namespace App\Observers;

use App\Models\Order;
use App\Models\User;
use App\Notifications\OrderStatusChanged;
use Illuminate\Support\Facades\Log;

class OrderObserver
{
    public function updated(Order $order): void
    {
        Log::info('OrderObserver::updated called', [
            'order_id' => $order->id,
            'dirty_fields' => $order->getDirty(),
            'is_status_dirty' => $order->isDirty('order_status_id')
        ]);

        if ($order->isDirty('order_status_id')) {
            $oldStatusId = $order->getOriginal('order_status_id');
            $newStatusId = $order->order_status_id;

            Log::info('Order status changed in Observer', [
                'order_id' => $order->id,
                'old_status_id' => $oldStatusId,
                'new_status_id' => $newStatusId,
                'user_id' => $order->user_id
            ]);

            $user = User::find($order->user_id);

            if (!$user) {
                Log::warning('User not found for order', ['order_id' => $order->id, 'user_id' => $order->user_id]);
                return;
            }

            $user->notify(new OrderStatusChanged($order, $oldStatusId, $newStatusId));

            Log::info('Notification sent from Observer', [
                'order_id' => $order->id,
                'user_id' => $order->user_id
            ]);
        }
    }
}
