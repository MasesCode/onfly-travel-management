<?php

namespace Tests\Unit\Models;

use App\Models\OrderStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_status_has_fillable_attributes(): void
    {
        $status = new OrderStatus();
        $fillable = $status->getFillable();

        $this->assertContains('name', $fillable);
        $this->assertContains('is_custom', $fillable);
    }

    public function test_order_status_casts_is_custom_to_boolean(): void
    {
        $status = OrderStatus::factory()->create([
            'is_custom' => true,
        ]);

        $this->assertIsBool($status->is_custom);
        $this->assertTrue($status->is_custom);
    }

    public function test_order_status_uses_soft_deletes(): void
    {
        $status = OrderStatus::factory()->create();
        $statusId = $status->id;

        $status->delete();

        $this->assertSoftDeleted('order_statuses', ['id' => $statusId]);
        $this->assertNull(OrderStatus::find($statusId));
        $this->assertNotNull(OrderStatus::withTrashed()->find($statusId));
    }

    public function test_order_status_can_be_restored_after_soft_delete(): void
    {
        $status = OrderStatus::factory()->create();
        $statusId = $status->id;

        $status->delete();
        $this->assertSoftDeleted('order_statuses', ['id' => $statusId]);

        $status->restore();
        $this->assertNotNull(OrderStatus::find($statusId));
    }

    public function test_order_status_factory_creates_valid_status(): void
    {
        $status = OrderStatus::factory()->create();

        $this->assertNotNull($status->name);
        $this->assertIsBool($status->is_custom);
    }

    public function test_order_status_factory_requested_state(): void
    {
        $status = OrderStatus::factory()->requested()->create();

        $this->assertEquals('requested', $status->name);
    }

    public function test_order_status_factory_approved_state(): void
    {
        $status = OrderStatus::factory()->approved()->create();

        $this->assertEquals('approved', $status->name);
    }

    public function test_order_status_factory_cancelled_state(): void
    {
        $status = OrderStatus::factory()->cancelled()->create();

        $this->assertEquals('cancelled', $status->name);
    }

    public function test_order_status_factory_custom_state(): void
    {
        $status = OrderStatus::factory()->custom()->create();

        $this->assertTrue($status->is_custom);
    }

    public function test_order_status_name_is_required(): void
    {
        $this->expectException(\Illuminate\Database\QueryException::class);
        OrderStatus::factory()->create(['name' => null]);
    }

    public function test_order_status_is_custom_defaults_to_false(): void
    {
        $status = OrderStatus::factory()->create();

        $this->assertFalse($status->is_custom);
    }
}
