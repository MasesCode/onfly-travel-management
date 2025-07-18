<?php

namespace Tests\Feature\Order;

use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_admin_can_update_order_status(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        $requestedStatus = OrderStatus::where('name', 'requested')->first();
        $approvedStatus = OrderStatus::where('name', 'approved')->first();

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'order_status_id' => $requestedStatus->id,
        ]);

        $response = $this->actingAs($user)
                         ->patchJson("/api/orders/{$order->id}/status", [
                             'status' => 'approved',
                         ]);

        $response->assertStatus(403);

        $response = $this->actingAs($admin)
                         ->patchJson("/api/orders/{$order->id}/status", [
                             'status' => 'approved',
                         ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'order_status_id' => $approvedStatus->id,
        ]);
    }

    public function test_order_status_update_logs_activity(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        $requestedStatus = OrderStatus::where('name', 'requested')->first();
        $approvedStatus = OrderStatus::where('name', 'approved')->first();

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'order_status_id' => $requestedStatus->id,
        ]);

        $response = $this->actingAs($admin)
                         ->patchJson("/api/orders/{$order->id}/status", [
                             'status' => 'approved',
                         ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'order_status_id' => $approvedStatus->id,
        ]);
    }

    public function test_cannot_cancel_approved_order(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        $approvedStatus = OrderStatus::where('name', 'approved')->first();

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'order_status_id' => $approvedStatus->id,
        ]);

        $response = $this->actingAs($admin)
                         ->patchJson("/api/orders/{$order->id}/status", [
                             'status' => 'cancelled',
                         ]);

        $response->assertStatus(422)
                 ->assertJsonFragment([
                     'error' => 'Não é possível cancelar um pedido já aprovado.',
                 ]);
    }

    public function test_cannot_change_status_of_cancelled_order(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        $cancelledStatus = OrderStatus::where('name', 'cancelled')->first();

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'order_status_id' => $cancelledStatus->id,
        ]);

        $response = $this->actingAs($admin)
                         ->patchJson("/api/orders/{$order->id}/status", [
                             'status' => 'approved',
                         ]);

        $response->assertStatus(422)
                 ->assertJsonFragment([
                     'error' => 'Não é possível alterar o status de um pedido cancelado.',
                 ]);
    }

    public function test_status_update_validates_allowed_statuses(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($admin)
                         ->patchJson("/api/orders/{$order->id}/status", [
                             'status' => 'invalid_status',
                         ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['status']);
    }

    public function test_admin_can_list_order_statuses(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)
                         ->getJson('/api/order-statuses');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     '*' => [
                         'id',
                         'name',
                         'is_custom',
                         'created_at',
                         'updated_at',
                     ],
                 ]);
    }

    public function test_admin_can_create_custom_order_status(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)
                         ->postJson('/api/order-statuses', [
                             'name' => 'custom_status',
                         ]);

        $response->assertStatus(201)
                 ->assertJsonFragment([
                     'name' => 'custom_status',
                     'is_custom' => true,
                 ]);

        $this->assertDatabaseHas('order_statuses', [
            'name' => 'custom_status',
            'is_custom' => true,
        ]);
    }

    public function test_admin_can_delete_custom_order_status(): void
    {
        $admin = User::factory()->admin()->create();
        $customStatus = OrderStatus::create(['name' => 'custom_status', 'is_custom' => true]);

        $response = $this->actingAs($admin)
                         ->deleteJson("/api/order-statuses/{$customStatus->id}");

        $response->assertStatus(200);

        $this->assertSoftDeleted('order_statuses', [
            'id' => $customStatus->id,
        ]);
    }

    public function test_regular_user_cannot_access_order_status_management(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
                         ->getJson('/api/order-statuses');
        $response->assertStatus(403);

        $response = $this->actingAs($user)
                         ->postJson('/api/order-statuses', [
                             'name' => 'test_status',
                         ]);
        $response->assertStatus(403);

        $response = $this->actingAs($user)
                         ->deleteJson('/api/order-statuses/1');
        $response->assertStatus(403);
    }

    public function test_order_status_creation_validates_required_fields(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)
                         ->postJson('/api/order-statuses', []);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['name']);
    }

    public function test_order_status_name_must_be_unique(): void
    {
        $admin = User::factory()->admin()->create();
        OrderStatus::factory()->create(['name' => 'duplicate_status']);

        $response = $this->actingAs($admin)
                         ->postJson('/api/order-statuses', [
                             'name' => 'duplicate_status',
                         ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['name']);
    }

    public function test_unauthenticated_user_cannot_access_order_status_endpoints(): void
    {
        $response = $this->getJson('/api/order-statuses');
        $response->assertStatus(401);

        $response = $this->postJson('/api/order-statuses', []);
        $response->assertStatus(401);

        $response = $this->deleteJson('/api/order-statuses/1');
        $response->assertStatus(401);

        $response = $this->patchJson('/api/orders/1/status', []);
        $response->assertStatus(401);
    }
}
