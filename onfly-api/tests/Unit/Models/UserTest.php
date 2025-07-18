<?php

namespace Tests\Unit\Models;

use App\Models\User;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_has_fillable_attributes(): void
    {
        $user = new User();
        $fillable = $user->getFillable();

        $this->assertContains('name', $fillable);
        $this->assertContains('email', $fillable);
        $this->assertContains('password', $fillable);
        $this->assertContains('is_admin', $fillable);
    }

    public function test_user_has_hidden_attributes(): void
    {
        $user = new User();
        $hidden = $user->getHidden();

        $this->assertContains('password', $hidden);
        $this->assertContains('remember_token', $hidden);
    }

    public function test_user_casts_attributes_correctly(): void
    {
        $user = User::factory()->create([
            'is_admin' => true,
        ]);

        $this->assertIsBool($user->is_admin);
        $this->assertTrue($user->is_admin);
    }

    public function test_is_admin_method_returns_boolean(): void
    {
        $adminUser = User::factory()->admin()->create();
        $regularUser = User::factory()->create(['is_admin' => false]);

        $this->assertTrue($adminUser->isAdmin());
        $this->assertFalse($regularUser->isAdmin());
    }

    public function test_user_has_orders_relationship(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(Order::class, $user->orders->first());
        $this->assertEquals($order->id, $user->orders->first()->id);
    }

    public function test_user_can_have_multiple_orders(): void
    {
        $user = User::factory()->create();
        Order::factory()->count(3)->create(['user_id' => $user->id]);

        $this->assertCount(3, $user->orders);
    }

    public function test_user_password_is_hashed_on_creation(): void
    {
        $user = User::factory()->create(['password' => 'plain_password']);

        $this->assertNotEquals('plain_password', $user->password);
        $this->assertTrue(Hash::check('plain_password', $user->password));
    }

    public function test_user_has_api_tokens_trait(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token');

        $this->assertNotNull($token->plainTextToken);
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'tokenable_type' => 'App\Models\User',
            'name' => 'test-token',
        ]);
    }

    public function test_user_has_notifications_trait(): void
    {
        $user = User::factory()->create();

        $this->assertIsArray($user->notifications->toArray());
    }

    public function test_user_factory_creates_valid_user(): void
    {
        $user = User::factory()->create();

        $this->assertNotNull($user->name);
        $this->assertNotNull($user->email);
        $this->assertNotNull($user->password);
        $this->assertFalse($user->is_admin);
    }

    public function test_admin_factory_creates_admin_user(): void
    {
        $admin = User::factory()->admin()->create();

        $this->assertTrue($admin->is_admin);
        $this->assertTrue($admin->isAdmin());
    }

    public function test_user_email_is_unique(): void
    {
        $email = 'test@example.com';
        User::factory()->create(['email' => $email]);

        $this->expectException(\Illuminate\Database\QueryException::class);
        User::factory()->create(['email' => $email]);
    }

    public function test_user_soft_deletes_work(): void
    {
        $user = User::factory()->create();
        $userId = $user->id;

        $user->delete();

        $this->assertSoftDeleted('users', ['id' => $userId]);
        $this->assertNull(User::find($userId));
        $this->assertNotNull(User::withTrashed()->find($userId));
    }

    public function test_user_can_be_restored_after_soft_delete(): void
    {
        $user = User::factory()->create();
        $userId = $user->id;

        $user->delete();
        $this->assertSoftDeleted('users', ['id' => $userId]);

        $user->restore();
        $this->assertNotNull(User::find($userId));
    }

    public function test_user_name_is_required(): void
    {
        $this->expectException(\Illuminate\Database\QueryException::class);
        User::factory()->create(['name' => null]);
    }

    public function test_user_email_is_required(): void
    {
        $this->expectException(\Illuminate\Database\QueryException::class);
        User::factory()->create(['email' => null]);
    }

    public function test_user_password_is_required(): void
    {
        $this->expectException(\Illuminate\Database\QueryException::class);
        User::factory()->create(['password' => null]);
    }

    public function test_user_is_admin_defaults_to_false(): void
    {
        $user = User::factory()->create();

        $this->assertFalse($user->is_admin);
    }
}
