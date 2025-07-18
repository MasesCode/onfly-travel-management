<?php

namespace Tests\Feature\Integration;

use App\Models\User;
use App\Models\Order;
use App\Models\OrderStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class OrderWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_complete_order_workflow(): void
    {
        Notification::fake();

        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        $requestedStatus = OrderStatus::where('name', 'requested')->first();
        $approvedStatus = OrderStatus::where('name', 'approved')->first();

        $orderData = [
            'destination' => 'São Paulo',
            'departure_date' => now()->addDays(10)->format('Y-m-d'),
            'return_date' => now()->addDays(20)->format('Y-m-d'),
        ];

        $orderResponse = $this->actingAs($user)
                              ->postJson('/api/orders', $orderData);

        $orderResponse->assertStatus(201);
        $orderId = $orderResponse->json('id');

        $this->assertDatabaseHas('orders', [
            'id' => $orderId,
            'user_id' => $user->id,
            'destination' => 'São Paulo',
            'order_status_id' => $requestedStatus->id,
        ]);

        $statusResponse = $this->actingAs($admin)
                               ->patchJson("/api/orders/{$orderId}/status", [
                                   'status' => 'approved',
                               ]);

        $statusResponse->assertStatus(200);

        $this->assertDatabaseHas('orders', [
            'id' => $orderId,
            'order_status_id' => $approvedStatus->id,
        ]);

        $orderDetailsResponse = $this->actingAs($user)
                                     ->getJson("/api/orders/{$orderId}");

        $orderDetailsResponse->assertStatus(200)
                             ->assertJsonFragment([
                                 'destination' => 'São Paulo',
                             ]);
    }

    public function test_order_cannot_be_edited_after_approval(): void
    {
        $user = User::factory()->create();
        $admin = User::factory()->admin()->create();

        $requestedStatus = OrderStatus::where('name', 'requested')->first();

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'order_status_id' => $requestedStatus->id,
        ]);

        $editResponse = $this->actingAs($user)
                             ->putJson("/api/orders/{$order->id}", [
                                 'destination' => 'Updated Destination',
                             ]);

        $editResponse->assertStatus(200);

        $this->actingAs($admin)
             ->patchJson("/api/orders/{$order->id}/status", [
                 'status' => 'approved',
             ]);

        $editAfterApprovalResponse = $this->actingAs($user)
                                          ->putJson("/api/orders/{$order->id}", [
                                              'destination' => 'Another Update',
                                          ]);

        $editAfterApprovalResponse->assertStatus(403);
    }

    public function test_cancelled_order_cannot_be_modified(): void
    {
        $user = User::factory()->create();
        $admin = User::factory()->admin()->create();

        $requestedStatus = OrderStatus::where('name', 'requested')->first();

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'order_status_id' => $requestedStatus->id,
        ]);

        $this->actingAs($admin)
             ->patchJson("/api/orders/{$order->id}/status", [
                 'status' => 'cancelled',
             ]);

        $editResponse = $this->actingAs($user)
                             ->putJson("/api/orders/{$order->id}", [
                                 'destination' => 'Updated Destination',
                             ]);

        $editResponse->assertStatus(403);

        $statusChangeResponse = $this->actingAs($admin)
                                     ->patchJson("/api/orders/{$order->id}/status", [
                                         'status' => 'approved',
                                     ]);

        $statusChangeResponse->assertStatus(422);
    }

    public function test_order_listing_with_filters_and_pagination(): void
    {
        $user = User::factory()->create();
        $admin = User::factory()->admin()->create();

        $requestedStatus = OrderStatus::where('name', 'requested')->first();
        $approvedStatus = OrderStatus::where('name', 'approved')->first();

        Order::factory()->count(5)->create([
            'user_id' => $user->id,
            'order_status_id' => $requestedStatus->id,
            'destination' => 'São Paulo',
        ]);

        Order::factory()->count(3)->create([
            'user_id' => $user->id,
            'order_status_id' => $approvedStatus->id,
            'destination' => 'Rio de Janeiro',
        ]);

        Order::factory()->count(2)->create([
            'user_id' => $admin->id,
            'order_status_id' => $requestedStatus->id,
            'destination' => 'Belo Horizonte',
        ]);

        $userFilteredResponse = $this->actingAs($user)
                                     ->getJson('/api/orders?status=requested&destination=São Paulo');

        $userFilteredResponse->assertStatus(200);
        $userOrders = $userFilteredResponse->json('data');
        $this->assertCount(5, $userOrders);

        $adminAllOrdersResponse = $this->actingAs($admin)
                                       ->getJson('/api/orders?per_page=5');

        $adminAllOrdersResponse->assertStatus(200);
        $adminOrders = $adminAllOrdersResponse->json('data');
        $this->assertCount(5, $adminOrders);
        $this->assertEquals(10, $adminAllOrdersResponse->json('total'));
    }

    public function test_data_consistency_across_relationships(): void
    {
        $user = User::factory()->create();
        $admin = User::factory()->admin()->create();

        $requestedStatus = OrderStatus::where('name', 'requested')->first();
        $approvedStatus = OrderStatus::where('name', 'approved')->first();

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'order_status_id' => $requestedStatus->id,
            'requester_name' => $user->name,
        ]);

        $this->assertEquals($user->name, $order->requester_name);
        $this->assertEquals($user->id, $order->user->id);
        $this->assertEquals($requestedStatus->id, $order->status->id);
        $this->assertEquals('requested', $order->status->name);

        $this->actingAs($admin)
             ->patchJson("/api/orders/{$order->id}/status", [
                 'status' => 'approved',
             ]);

        $order->refresh();
        $this->assertEquals($approvedStatus->id, $order->status->id);
        $this->assertEquals('approved', $order->status->name);
    }
}
