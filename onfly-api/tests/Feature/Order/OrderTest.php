<?php

namespace Tests\Feature\Order;

use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    private const ORDERS_ENDPOINT = '/api/orders';

    public function test_admin_can_list_all_orders(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        Order::factory()->create(['user_id' => $admin->id]);
        Order::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($admin)
                         ->getJson(self::ORDERS_ENDPOINT);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => [
                         '*' => [
                             'id',
                             'user_id',
                             'requester',
                             'destination',
                             'start_date',
                             'end_date',
                             'status',
                             'notes',
                             'created_at',
                             'updated_at',
                         ],
                     ],
                 ]);

        $this->assertCount(2, $response->json('data'));
    }

    public function test_user_can_list_only_their_orders(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $userOrder = Order::factory()->create(['user_id' => $user->id]);
        Order::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user)
                         ->getJson(self::ORDERS_ENDPOINT);

        $response->assertStatus(200);

        $orders = $response->json('data');
        $this->assertCount(1, $orders);
        $this->assertEquals($userOrder->id, $orders[0]['id']);
    }

    public function test_user_can_create_order(): void
    {
        $user = User::factory()->create();
        $status = OrderStatus::where('name', 'requested')->first();

        $orderData = [
            'destination' => 'São Paulo',
            'departure_date' => now()->addDays(10)->format('Y-m-d'),
            'return_date' => now()->addDays(20)->format('Y-m-d'),
        ];

        $response = $this->actingAs($user)
                         ->postJson(self::ORDERS_ENDPOINT, $orderData);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'id',
                     'user_id',
                     'order_status_id',
                     'requester_name',
                     'destination',
                     'departure_date',
                     'return_date',
                 ]);

        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'destination' => 'São Paulo',
            'requester_name' => $user->name,
            'order_status_id' => $status->id,
        ]);
    }

    public function test_admin_can_create_order_for_another_user(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        $orderData = [
            'user_id' => $user->id,
            'destination' => 'Rio de Janeiro',
            'departure_date' => now()->addDays(10)->format('Y-m-d'),
            'return_date' => now()->addDays(20)->format('Y-m-d'),
        ];

        $response = $this->actingAs($admin)
                         ->postJson(self::ORDERS_ENDPOINT, $orderData);

        $response->assertStatus(201);

        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'destination' => 'Rio de Janeiro',
            'requester_name' => $user->name,
        ]);
    }

    public function test_user_can_update_their_own_order(): void
    {
        $user = User::factory()->create();
        $status = OrderStatus::factory()->requested()->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'order_status_id' => $status->id,
        ]);

        $updateData = [
            'destination' => 'Updated Destination',
            'departure_date' => now()->addDays(15)->format('Y-m-d'),
            'return_date' => now()->addDays(25)->format('Y-m-d'),
        ];

        $response = $this->actingAs($user)
                         ->putJson("/api/orders/{$order->id}", $updateData);

        $response->assertStatus(200);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'destination' => 'Updated Destination',
        ]);
    }

    public function test_user_cannot_update_other_users_order(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $otherUser->id]);

        $updateData = [
            'destination' => 'Updated Destination',
        ];

        $response = $this->actingAs($user)
                         ->putJson("/api/orders/{$order->id}", $updateData);

        $response->assertStatus(403);
    }

    public function test_user_cannot_update_approved_order(): void
    {
        $user = User::factory()->create();
        $approvedStatus = OrderStatus::factory()->approved()->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'order_status_id' => $approvedStatus->id,
        ]);

        $updateData = [
            'destination' => 'Updated Destination',
        ];

        $response = $this->actingAs($user)
                         ->putJson("/api/orders/{$order->id}", $updateData);

        $response->assertStatus(403);
    }

    public function test_user_cannot_update_cancelled_order(): void
    {
        $user = User::factory()->create();
        $cancelledStatus = OrderStatus::factory()->cancelled()->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'order_status_id' => $cancelledStatus->id,
        ]);

        $updateData = [
            'destination' => 'Updated Destination',
        ];

        $response = $this->actingAs($user)
                         ->putJson("/api/orders/{$order->id}", $updateData);

        $response->assertStatus(403);
    }

    public function test_admin_can_update_any_order(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $updateData = [
            'destination' => 'Admin Updated Destination',
        ];

        $response = $this->actingAs($admin)
                         ->putJson("/api/orders/{$order->id}", $updateData);

        $response->assertStatus(200);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'destination' => 'Admin Updated Destination',
        ]);
    }

    public function test_user_can_delete_their_own_order(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)
                         ->deleteJson("/api/orders/{$order->id}");

        $response->assertStatus(200);

        $this->assertSoftDeleted('orders', [
            'id' => $order->id,
        ]);
    }

    public function test_user_cannot_delete_other_users_order(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user)
                         ->deleteJson("/api/orders/{$order->id}");

        $response->assertStatus(403);
    }

    public function test_admin_can_delete_any_order(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($admin)
                         ->deleteJson("/api/orders/{$order->id}");

        $response->assertStatus(200);

        $this->assertSoftDeleted('orders', [
            'id' => $order->id,
        ]);
    }

    public function test_user_can_view_their_own_order(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)
                         ->getJson("/api/orders/{$order->id}");

        $response->assertStatus(200)
                 ->assertJson([
                     'id' => $order->id,
                     'user_id' => $user->id,
                 ]);
    }

    public function test_user_cannot_view_other_users_order(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user)
                         ->getJson("/api/orders/{$order->id}");

        $response->assertStatus(404);
    }

    public function test_admin_can_view_any_order(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($admin)
                         ->getJson("/api/orders/{$order->id}");

        $response->assertStatus(200)
                 ->assertJson([
                     'id' => $order->id,
                     'user_id' => $user->id,
                 ]);
    }

    public function test_orders_can_be_filtered_by_status(): void
    {
        $user = User::factory()->create();
        $requestedStatus = OrderStatus::factory()->requested()->create();
        $approvedStatus = OrderStatus::factory()->approved()->create();

        $requestedOrder = Order::factory()->create([
            'user_id' => $user->id,
            'order_status_id' => $requestedStatus->id,
        ]);

        $approvedOrder = Order::factory()->create([
            'user_id' => $user->id,
            'order_status_id' => $approvedStatus->id,
        ]);

        $response = $this->actingAs($user)
                         ->getJson('/api/orders?status=requested');

        $response->assertStatus(200);

        $orders = $response->json('data');
        $this->assertCount(1, $orders);
        $this->assertEquals('requested', $orders[0]['status']);
    }

    public function test_orders_can_be_filtered_by_destination(): void
    {
        $user = User::factory()->create();

        $order1 = Order::factory()->create([
            'user_id' => $user->id,
            'destination' => 'São Paulo',
        ]);

        $order2 = Order::factory()->create([
            'user_id' => $user->id,
            'destination' => 'Rio de Janeiro',
        ]);

        $response = $this->actingAs($user)
                         ->getJson('/api/orders?destination=São Paulo');

        $response->assertStatus(200);

        $orders = $response->json('data');
        $this->assertCount(1, $orders);
        $this->assertEquals('São Paulo', $orders[0]['destination']);
    }

    public function test_order_creation_validates_required_fields(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
                         ->postJson('/api/orders', []);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors([
                     'destination',
                     'departure_date',
                     'return_date',
                 ]);
    }

    public function test_order_creation_validates_date_logic(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
                         ->postJson('/api/orders', [
                             'destination' => 'São Paulo',
                             'departure_date' => now()->subDays(1)->format('Y-m-d'),
                             'return_date' => now()->addDays(10)->format('Y-m-d'),
                         ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['departure_date']);

        $response = $this->actingAs($user)
                         ->postJson('/api/orders', [
                             'destination' => 'São Paulo',
                             'departure_date' => now()->addDays(10)->format('Y-m-d'),
                             'return_date' => now()->addDays(5)->format('Y-m-d'),
                         ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['return_date']);
    }

    public function test_orders_are_paginated(): void
    {
        $user = User::factory()->create();

        Order::factory()->count(15)->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)
                         ->getJson('/api/orders?per_page=5');

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

    public function test_unauthenticated_user_cannot_access_orders(): void
    {
        $response = $this->getJson('/api/orders');
        $response->assertStatus(401);

        $response = $this->postJson('/api/orders', []);
        $response->assertStatus(401);

        $response = $this->putJson('/api/orders/1', []);
        $response->assertStatus(401);

        $response = $this->deleteJson('/api/orders/1');
        $response->assertStatus(401);

        $response = $this->getJson('/api/orders/1');
        $response->assertStatus(401);
    }
}
