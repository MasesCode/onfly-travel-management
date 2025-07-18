<?php

namespace Tests\Feature\Validation;

use App\Models\User;
use App\Models\Order;
use App\Models\OrderStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_creation_validation(): void
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

        $response = $this->actingAs($user)
                         ->postJson('/api/orders', [
                             'destination' => '',
                             'departure_date' => 'invalid-date',
                             'return_date' => 'invalid-date',
                         ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors([
                     'destination',
                     'departure_date',
                     'return_date',
                 ]);

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

        $response = $this->actingAs($user)
                         ->postJson('/api/orders', [
                             'user_id' => 999,
                             'destination' => 'São Paulo',
                             'departure_date' => now()->addDays(10)->format('Y-m-d'),
                             'return_date' => now()->addDays(15)->format('Y-m-d'),
                         ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['user_id']);
    }

    public function test_order_update_validation(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)
                         ->putJson("/api/orders/{$order->id}", [
                             'departure_date' => 'invalid-date',
                             'return_date' => 'invalid-date',
                         ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors([
                     'departure_date',
                     'return_date',
                 ]);

        $response = $this->actingAs($user)
                         ->putJson("/api/orders/{$order->id}", [
                             'departure_date' => now()->subDays(1)->format('Y-m-d'),
                             'return_date' => now()->addDays(10)->format('Y-m-d'),
                         ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['departure_date']);

        $response = $this->actingAs($user)
                         ->putJson("/api/orders/{$order->id}", [
                             'departure_date' => now()->addDays(10)->format('Y-m-d'),
                             'return_date' => now()->addDays(5)->format('Y-m-d'),
                         ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['return_date']);
    }

    public function test_order_status_update_validation(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($admin)
                         ->patchJson("/api/orders/{$order->id}/status", []);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['status']);

        $response = $this->actingAs($admin)
                         ->patchJson("/api/orders/{$order->id}/status", [
                             'status' => 'invalid_status',
                         ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['status']);
    }

    public function test_user_registration_validation(): void
    {
        $response = $this->postJson('/api/register', []);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors([
                     'name',
                     'email',
                     'password',
                 ]);
    }

    public function test_user_login_validation(): void
    {
        $response = $this->postJson('/api/login', []);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors([
                     'email',
                     'password',
                 ]);

        $response = $this->postJson('/api/login', [
            'email' => 'invalid-email',
            'password' => '',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors([
                     'email',
                     'password',
                 ]);
    }

    public function test_profile_update_validation(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create(['email' => 'other@example.com']);

        $response = $this->actingAs($user)
                         ->putJson('/api/profile', []);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors([
                     'name',
                     'email',
                 ]);

        $response = $this->actingAs($user)
                         ->putJson('/api/profile', [
                             'name' => '',
                             'email' => 'invalid-email',
                         ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors([
                     'name',
                     'email',
                 ]);

        $response = $this->actingAs($user)
                         ->putJson('/api/profile', [
                             'name' => 'João Silva',
                             'email' => 'other@example.com',
                         ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['email']);
    }

    public function test_password_update_validation(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
                         ->putJson('/api/profile/password', []);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors([
                     'current_password',
                     'password',
                 ]);

        $response = $this->actingAs($user)
                         ->putJson('/api/profile/password', [
                             'current_password' => '',
                             'password' => '123',
                             'password_confirmation' => '456',
                         ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors([
                     'current_password',
                     'password',
                 ]);

        $response = $this->actingAs($user)
                         ->putJson('/api/profile/password', [
                             'current_password' => 'wrong_password',
                             'password' => 'new_password123',
                             'password_confirmation' => 'new_password123',
                         ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['current_password']);
    }

    public function test_admin_user_creation_validation(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)
                         ->postJson('/api/users', []);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors([
                     'name',
                     'email',
                     'password',
                 ]);

        $response = $this->actingAs($admin)
                         ->postJson('/api/users', [
                             'name' => '',
                             'email' => 'invalid-email',
                             'password' => '123',
                             'password_confirmation' => '456',
                         ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors([
                     'name',
                     'email',
                     'password',
                 ]);

        User::factory()->create(['email' => 'existing@example.com']);

        $response = $this->actingAs($admin)
                         ->postJson('/api/users', [
                             'name' => 'João Silva',
                             'email' => 'existing@example.com',
                             'password' => 'password123',
                             'password_confirmation' => 'password123',
                         ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['email']);
    }

    public function test_admin_user_update_validation(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        $otherUser = User::factory()->create(['email' => 'other@example.com']);

        $response = $this->actingAs($admin)
                         ->putJson("/api/users/{$user->id}", [
                             'name' => '',
                             'email' => 'invalid-email',
                         ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors([
                     'name',
                     'email',
                 ]);

        $response = $this->actingAs($admin)
                         ->putJson("/api/users/{$user->id}", [
                             'name' => 'João Silva',
                             'email' => 'other@example.com',
                         ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['email']);

        $response = $this->actingAs($admin)
                         ->putJson("/api/users/{$user->id}", [
                             'name' => 'João Silva',
                             'email' => 'updated@example.com',
                             'password' => '123',
                             'password_confirmation' => '456',
                         ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['password']);
    }

    public function test_order_status_creation_validation(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)
                         ->postJson('/api/order-statuses', []);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['name']);

        $response = $this->actingAs($admin)
                         ->postJson('/api/order-statuses', [
                             'name' => '',
                         ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['name']);

        OrderStatus::factory()->create(['name' => 'existing_status']);

        $response = $this->actingAs($admin)
                         ->postJson('/api/order-statuses', [
                             'name' => 'existing_status',
                         ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['name']);
    }

    public function test_string_length_validation(): void
    {
        $user = User::factory()->create();
        $admin = User::factory()->admin()->create();

        $longString = str_repeat('a', 300);

        $response = $this->postJson('/api/register', [
            'name' => $longString,
            'email' => $longString . '@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors([
                     'name',
                     'email',
                 ]);

        $response = $this->actingAs($user)
                         ->postJson('/api/orders', [
                             'destination' => $longString,
                             'departure_date' => now()->addDays(10)->format('Y-m-d'),
                             'return_date' => now()->addDays(15)->format('Y-m-d'),
                         ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['destination']);

        $response = $this->actingAs($admin)
                         ->postJson('/api/order-statuses', [
                             'name' => $longString,
                         ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['name']);
    }
}
