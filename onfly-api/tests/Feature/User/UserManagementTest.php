<?php

namespace Tests\Feature\User;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_list_all_users(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->count(5)->create();

        $response = $this->actingAs($admin)
                         ->getJson('/api/users');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => [
                         '*' => [
                             'id',
                             'name',
                             'email',
                             'is_admin',
                             'created_at',
                             'updated_at',
                         ],
                     ],
                 ]);

        $this->assertCount(6, $response->json('data'));
    }

    public function test_admin_can_create_new_user(): void
    {
        $admin = User::factory()->admin()->create();

        $userData = [
            'name' => 'Novo Usuário',
            'email' => 'novo@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'is_admin' => false,
        ];

        $response = $this->actingAs($admin)
                         ->postJson('/api/users', $userData);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'id',
                     'name',
                     'email',
                     'is_admin',
                     'created_at',
                     'updated_at',
                 ]);

        $this->assertDatabaseHas('users', [
            'name' => 'Novo Usuário',
            'email' => 'novo@example.com',
            'is_admin' => false,
        ]);
    }

    public function test_admin_can_update_user(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        $updateData = [
            'name' => 'Nome Atualizado',
            'email' => 'atualizado@example.com',
            'is_admin' => true,
        ];

        $response = $this->actingAs($admin)
                         ->putJson("/api/users/{$user->id}", $updateData);

        $response->assertStatus(200)
                 ->assertJsonFragment([
                     'name' => 'Nome Atualizado',
                     'email' => 'atualizado@example.com',
                     'is_admin' => true,
                 ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Nome Atualizado',
            'email' => 'atualizado@example.com',
            'is_admin' => true,
        ]);
    }

    public function test_admin_can_delete_user(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        $response = $this->actingAs($admin)
                         ->deleteJson("/api/users/{$user->id}");

        $response->assertStatus(200)
                 ->assertJson([
                     'message' => 'Usuário excluído com sucesso.',
                 ]);

        $this->assertSoftDeleted('users', [
            'id' => $user->id,
        ]);
    }

    public function test_admin_cannot_delete_themselves(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)
                         ->deleteJson("/api/users/{$admin->id}");

        $response->assertStatus(422)
                 ->assertJsonFragment([
                     'error' => 'Você não pode excluir sua própria conta.',
                 ]);
    }

    public function test_regular_user_cannot_access_user_management(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $response = $this->actingAs($user)
                         ->getJson('/api/users');
        $response->assertStatus(403);

        $response = $this->actingAs($user)
                         ->postJson('/api/users', []);
        $response->assertStatus(403);

        $response = $this->actingAs($user)
                         ->putJson("/api/users/{$otherUser->id}", []);
        $response->assertStatus(403);

        $response = $this->actingAs($user)
                         ->deleteJson("/api/users/{$otherUser->id}");
        $response->assertStatus(403);
    }

    public function test_admin_routes_work_with_admin_prefix(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->count(3)->create();

        $response = $this->actingAs($admin)
                         ->getJson('/api/admin/users');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => [
                         '*' => [
                             'id',
                             'name',
                             'email',
                             'is_admin',
                         ],
                     ],
                 ]);

        $this->assertCount(4, $response->json('data'));
    }

    public function test_user_creation_validates_required_fields(): void
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
    }

    public function test_user_creation_validates_email_uniqueness(): void
    {
        $admin = User::factory()->admin()->create();
        $existingUser = User::factory()->create(['email' => 'existing@example.com']);

        $response = $this->actingAs($admin)
                         ->postJson('/api/users', [
                             'name' => 'Novo Usuário',
                             'email' => 'existing@example.com',
                             'password' => 'password123',
                             'password_confirmation' => 'password123',
                         ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['email']);
    }

    public function test_user_update_validates_email_uniqueness(): void
    {
        $admin = User::factory()->admin()->create();
        $user1 = User::factory()->create(['email' => 'user1@example.com']);
        $user2 = User::factory()->create(['email' => 'user2@example.com']);

        $response = $this->actingAs($admin)
                         ->putJson("/api/users/{$user1->id}", [
                             'name' => 'Nome Atualizado',
                             'email' => 'user2@example.com',
                         ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['email']);
    }

    public function test_user_update_allows_same_email(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create(['email' => 'user@example.com']);

        $response = $this->actingAs($admin)
                         ->putJson("/api/users/{$user->id}", [
                             'name' => 'Nome Atualizado',
                             'email' => 'user@example.com',
                         ]);

        $response->assertStatus(200)
                 ->assertJsonFragment([
                     'name' => 'Nome Atualizado',
                     'email' => 'user@example.com',
                 ]);
    }

    public function test_user_update_with_password_change(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        $response = $this->actingAs($admin)
                         ->putJson("/api/users/{$user->id}", [
                             'name' => 'Nome Atualizado',
                             'email' => 'novo@example.com',
                             'password' => 'nova_senha123',
                             'password_confirmation' => 'nova_senha123',
                         ]);

        $response->assertStatus(200);

        $user->refresh();
        $this->assertTrue(Hash::check('nova_senha123', $user->password));
    }

    public function test_users_are_paginated(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->count(25)->create();

        $response = $this->actingAs($admin)
                         ->getJson('/api/users?per_page=10');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data',
                     'current_page',
                     'per_page',
                     'total',
                     'last_page',
                 ]);

        $this->assertCount(10, $response->json('data'));
        $this->assertEquals(10, $response->json('per_page'));
        $this->assertEquals(26, $response->json('total'));
    }

    public function test_users_can_be_filtered_by_admin_status(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->count(3)->create();
        User::factory()->admin()->count(2)->create();

        $response = $this->actingAs($admin)
                         ->getJson('/api/users?is_admin=true');

        $response->assertStatus(200);

        $users = $response->json('data');
        $this->assertCount(3, $users);

        foreach ($users as $user) {
            $this->assertTrue($user['is_admin']);
        }
    }

    public function test_users_can_be_searched_by_name(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->create(['name' => 'João Silva']);
        User::factory()->create(['name' => 'Maria Santos']);
        User::factory()->create(['name' => 'Pedro Oliveira']);

        $response = $this->actingAs($admin)
                         ->getJson('/api/users?search=João');

        $response->assertStatus(200);

        $users = $response->json('data');
        $this->assertCount(1, $users);
        $this->assertEquals('João Silva', $users[0]['name']);
    }

    public function test_users_can_be_searched_by_email(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->create(['email' => 'joao@example.com']);
        User::factory()->create(['email' => 'maria@example.com']);
        User::factory()->create(['email' => 'pedro@example.com']);

        $response = $this->actingAs($admin)
                         ->getJson('/api/users?search=joao@example.com');

        $response->assertStatus(200);

        $users = $response->json('data');
        $this->assertCount(1, $users);
        $this->assertEquals('joao@example.com', $users[0]['email']);
    }

    public function test_unauthenticated_user_cannot_access_user_management(): void
    {
        $response = $this->getJson('/api/users');
        $response->assertStatus(401);

        $response = $this->postJson('/api/users', []);
        $response->assertStatus(401);

        $response = $this->putJson('/api/users/1', []);
        $response->assertStatus(401);

        $response = $this->deleteJson('/api/users/1');
        $response->assertStatus(401);
    }
}
