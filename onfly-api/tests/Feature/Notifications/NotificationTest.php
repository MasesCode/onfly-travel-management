<?php

namespace Tests\Feature\Notifications;

use App\Models\User;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Notifications\OrderStatusChanged;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_list_their_notifications(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create();
        $status = OrderStatus::factory()->approved()->create();

        $user->notify(new OrderStatusChanged($order, null, $status->id));

        $response = $this->actingAs($user)
                         ->getJson('/api/notifications');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => [
                         '*' => [
                             'id',
                             'type',
                             'data',
                             'read_at',
                             'created_at',
                         ],
                     ],
                 ]);
    }

    public function test_user_can_get_unread_notifications_count(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create();
        $approvedStatus = OrderStatus::factory()->approved()->create();
        $cancelledStatus = OrderStatus::factory()->cancelled()->create();

        $user->notify(new OrderStatusChanged($order, null, $approvedStatus->id));
        $user->notify(new OrderStatusChanged($order, null, $cancelledStatus->id));

        $response = $this->actingAs($user)
                         ->getJson('/api/notifications/unread-count');

        $response->assertStatus(200)
                 ->assertJson([
                     'unread_count' => 2,
                 ]);
    }

    public function test_user_can_mark_notification_as_read(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create();
        $status = OrderStatus::factory()->approved()->create();

        $user->notify(new OrderStatusChanged($order, null, $status->id));

        $notification = $user->notifications()->first();

        $response = $this->actingAs($user)
                         ->patchJson("/api/notifications/{$notification->id}/read");

        $response->assertStatus(200);

        $notification->refresh();
        $this->assertNotNull($notification->read_at);
    }

    public function test_user_can_mark_all_notifications_as_read(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create();
        $approvedStatus = OrderStatus::factory()->approved()->create();
        $cancelledStatus = OrderStatus::factory()->cancelled()->create();

        $user->notify(new OrderStatusChanged($order, null, $approvedStatus->id));
        $user->notify(new OrderStatusChanged($order, null, $cancelledStatus->id));

        $response = $this->actingAs($user)
                         ->patchJson('/api/notifications/mark-all-read');

        $response->assertStatus(200);

        $unreadCount = $user->unreadNotifications()->count();
        $this->assertEquals(0, $unreadCount);
    }

    public function test_user_can_delete_notification(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create();
        $status = OrderStatus::factory()->approved()->create();

        $user->notify(new OrderStatusChanged($order, null, $status->id));

        $notification = $user->notifications()->first();

        $response = $this->actingAs($user)
                         ->deleteJson("/api/notifications/{$notification->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('notifications', [
            'id' => $notification->id,
        ]);
    }

    public function test_user_can_delete_all_notifications(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create();
        $approvedStatus = OrderStatus::factory()->approved()->create();
        $cancelledStatus = OrderStatus::factory()->cancelled()->create();

        $user->notify(new OrderStatusChanged($order, null, $approvedStatus->id));
        $user->notify(new OrderStatusChanged($order, null, $cancelledStatus->id));

        $response = $this->actingAs($user)
                         ->deleteJson('/api/notifications');

        $response->assertStatus(200);

        $this->assertEquals(0, $user->notifications()->count());
    }

    public function test_user_cannot_access_other_users_notifications(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $order = Order::factory()->create();
        $status = OrderStatus::factory()->approved()->create();

        $user2->notify(new OrderStatusChanged($order, null, $status->id));

        $notification = $user2->notifications()->first();

        $response = $this->actingAs($user1)
                         ->patchJson("/api/notifications/{$notification->id}/read");

        $response->assertStatus(403);
    }

    public function test_user_cannot_delete_other_users_notifications(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $order = Order::factory()->create();
        $status = OrderStatus::factory()->approved()->create();

        $user2->notify(new OrderStatusChanged($order, null, $status->id));

        $notification = $user2->notifications()->first();

        $response = $this->actingAs($user1)
                         ->deleteJson("/api/notifications/{$notification->id}");

        $response->assertStatus(403);
    }

    public function test_notifications_are_sent_when_order_status_changes(): void
    {
        Notification::fake();

        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        $requestedStatus = OrderStatus::factory()->requested()->create();
        $approvedStatus = OrderStatus::factory()->approved()->create();

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'order_status_id' => $requestedStatus->id,
        ]);

        $this->actingAs($admin)
             ->patchJson("/api/orders/{$order->id}/status", [
                 'status' => 'approved',
             ]);

        Notification::assertSentTo($user, OrderStatusChanged::class);
    }

    public function test_notifications_are_paginated(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create();
        $status = OrderStatus::factory()->approved()->create();

        for ($i = 0; $i < 15; $i++) {
            $user->notify(new OrderStatusChanged($order, null, $status->id));
        }

        $response = $this->actingAs($user)
                         ->getJson('/api/notifications?per_page=5');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data',
                     'current_page',
                     'per_page',
                     'total',
                     'last_page',
                 ]);

        $this->assertCount(5, $response->json('data'));
        $this->assertEquals(5, $response->json('per_page'));
        $this->assertEquals(15, $response->json('total'));
    }

    public function test_only_unread_notifications_count_towards_unread_count(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create();
        $approvedStatus = OrderStatus::factory()->approved()->create();
        $cancelledStatus = OrderStatus::factory()->cancelled()->create();

        $user->notify(new OrderStatusChanged($order, null, $approvedStatus->id));
        $user->notify(new OrderStatusChanged($order, null, $cancelledStatus->id));

        $notification = $user->notifications()->first();
        $notification->markAsRead();

        $response = $this->actingAs($user)
                         ->getJson('/api/notifications/unread-count');

        $response->assertStatus(200)
                 ->assertJson([
                     'unread_count' => 1,
                 ]);
    }

    public function test_unauthenticated_user_cannot_access_notifications(): void
    {
        $response = $this->getJson('/api/notifications');
        $response->assertStatus(401);

        $response = $this->getJson('/api/notifications/unread-count');
        $response->assertStatus(401);

        $response = $this->patchJson('/api/notifications/1/read');
        $response->assertStatus(401);

        $response = $this->patchJson('/api/notifications/mark-all-read');
        $response->assertStatus(401);

        $response = $this->deleteJson('/api/notifications/1');
        $response->assertStatus(401);

        $response = $this->deleteJson('/api/notifications');
        $response->assertStatus(401);
    }
}
