<?php

namespace Tests\Unit\Models;

use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_has_fillable_attributes(): void
    {
        $order = new Order();
        $fillable = $order->getFillable();

        $this->assertContains('user_id', $fillable);
        $this->assertContains('order_status_id', $fillable);
        $this->assertContains('requester_name', $fillable);
        $this->assertContains('destination', $fillable);
        $this->assertContains('departure_date', $fillable);
        $this->assertContains('return_date', $fillable);
    }

    public function test_order_has_appends_attributes(): void
    {
        $order = new Order();
        $appends = $order->getAppends();

        $this->assertContains('start_date', $appends);
        $this->assertContains('end_date', $appends);
        $this->assertContains('requester', $appends);
        $this->assertContains('notes', $appends);
    }

    public function test_order_casts_dates_correctly(): void
    {
        $order = Order::factory()->create([
            'departure_date' => '2025-01-01',
            'return_date' => '2025-01-10',
        ]);

        $this->assertInstanceOf(\Carbon\Carbon::class, $order->departure_date);
        $this->assertInstanceOf(\Carbon\Carbon::class, $order->return_date);
    }

    public function test_order_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $order->user);
        $this->assertEquals($user->id, $order->user->id);
    }

    public function test_order_belongs_to_status(): void
    {
        $status = OrderStatus::factory()->create();
        $order = Order::factory()->create(['order_status_id' => $status->id]);

        $this->assertInstanceOf(OrderStatus::class, $order->status);
        $this->assertEquals($status->id, $order->status->id);
    }

    public function test_order_start_date_accessor(): void
    {
        $order = Order::factory()->create([
            'departure_date' => '2025-01-01',
        ]);

        $this->assertEquals('2025-01-01', $order->start_date);
    }

    public function test_order_end_date_accessor(): void
    {
        $order = Order::factory()->create([
            'return_date' => '2025-01-10',
        ]);

        $this->assertEquals('2025-01-10', $order->end_date);
    }

    public function test_order_requester_accessor(): void
    {
        $order = Order::factory()->create([
            'requester_name' => 'João Silva',
        ]);

        $this->assertEquals('João Silva', $order->requester);
    }

    public function test_order_notes_accessor_returns_empty_string(): void
    {
        $order = Order::factory()->create();

        $this->assertEquals('', $order->notes);
    }

    public function test_order_uses_soft_deletes(): void
    {
        $order = Order::factory()->create();
        $orderId = $order->id;

        $order->delete();

        $this->assertSoftDeleted('orders', ['id' => $orderId]);
        $this->assertNull(Order::find($orderId));
        $this->assertNotNull(Order::withTrashed()->find($orderId));
    }

    public function test_order_can_be_restored_after_soft_delete(): void
    {
        $order = Order::factory()->create();
        $orderId = $order->id;

        $order->delete();
        $this->assertSoftDeleted('orders', ['id' => $orderId]);

        $order->restore();
        $this->assertNotNull(Order::find($orderId));
    }

    public function test_order_factory_creates_valid_order(): void
    {
        $order = Order::factory()->create();

        $this->assertNotNull($order->user_id);
        $this->assertNotNull($order->order_status_id);
        $this->assertNotNull($order->requester_name);
        $this->assertNotNull($order->destination);
        $this->assertNotNull($order->departure_date);
        $this->assertNotNull($order->return_date);
    }

    public function test_order_departure_date_is_required(): void
    {
        $this->expectException(\Illuminate\Database\QueryException::class);
        Order::factory()->create(['departure_date' => null]);
    }

    public function test_order_return_date_is_required(): void
    {
        $this->expectException(\Illuminate\Database\QueryException::class);
        Order::factory()->create(['return_date' => null]);
    }

    public function test_order_destination_is_required(): void
    {
        $this->expectException(\Illuminate\Database\QueryException::class);
        Order::factory()->create(['destination' => null]);
    }

    public function test_order_user_id_is_required(): void
    {
        $this->expectException(\Illuminate\Database\QueryException::class);
        Order::factory()->create(['user_id' => null]);
    }

    public function test_order_order_status_id_is_required(): void
    {
        $this->expectException(\Illuminate\Database\QueryException::class);
        Order::factory()->create(['order_status_id' => null]);
    }

    public function test_order_requester_name_is_required(): void
    {
        $this->expectException(\Illuminate\Database\QueryException::class);
        Order::factory()->create(['requester_name' => null]);
    }

    public function test_order_json_serialization_includes_appends(): void
    {
        $order = Order::factory()->create([
            'departure_date' => '2025-01-01',
            'return_date' => '2025-01-10',
            'requester_name' => 'João Silva',
        ]);

        $orderArray = $order->toArray();

        $this->assertArrayHasKey('start_date', $orderArray);
        $this->assertArrayHasKey('end_date', $orderArray);
        $this->assertArrayHasKey('requester', $orderArray);
        $this->assertArrayHasKey('notes', $orderArray);
        $this->assertEquals('2025-01-01', $orderArray['start_date']);
        $this->assertEquals('2025-01-10', $orderArray['end_date']);
        $this->assertEquals('João Silva', $orderArray['requester']);
        $this->assertEquals('', $orderArray['notes']);
    }
}
