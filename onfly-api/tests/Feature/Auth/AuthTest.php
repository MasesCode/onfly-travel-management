<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register(): void
    {
        $userData = [
            'name' => 'João Silva',
            'email' => 'joao@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/register', $userData);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'user' => [
                         'id',
                         'name',
                         'email',
                         'is_admin',
                         'created_at',
                         'updated_at',
                     ],
                     'token',
                 ]);

        $this->assertDatabaseHas('users', [
            'name' => 'João Silva',
            'email' => 'joao@example.com',
            'is_admin' => false,
        ]);
    }

    public function test_user_can_login(): void
    {
        $user = User::factory()->create([
            'email' => 'joao@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'joao@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'user' => [
                         'id',
                         'name',
                         'email',
                         'is_admin',
                     ],
                     'token',
                 ]);

        $this->assertEquals($user->id, $response->json('user.id'));
    }

    public function test_user_cannot_login_with_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'joao@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'joao@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors('email');
    }

    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('api-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/logout');

        $response->assertStatus(200)
                 ->assertJson([
                     'message' => 'Logout realizado com sucesso.',
                 ]);

        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'tokenable_type' => 'App\Models\User',
        ]);
    }

    public function test_user_can_get_their_profile(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('api-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/user');

        $response->assertStatus(200)
                 ->assertJson([
                     'id' => $user->id,
                     'name' => $user->name,
                     'email' => $user->email,
                     'is_admin' => $user->is_admin,
                 ]);
    }

    public function test_user_can_update_profile(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('api-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson('/api/profile', [
            'name' => 'João Silva Updated',
            'email' => 'joao.updated@example.com',
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'user' => [
                         'name' => 'João Silva Updated',
                         'email' => 'joao.updated@example.com',
                     ],
                     'message' => 'Perfil atualizado com sucesso.',
                 ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'João Silva Updated',
            'email' => 'joao.updated@example.com',
        ]);
    }

    public function test_user_can_update_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('old-password'),
        ]);
        $token = $user->createToken('api-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson('/api/profile/password', [
            'current_password' => 'old-password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'message' => 'Senha atualizada com sucesso.',
                 ]);

        $user->refresh();
        $this->assertTrue(Hash::check('new-password', $user->password));
    }

    public function test_user_cannot_update_password_with_wrong_current_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('old-password'),
        ]);
        $token = $user->createToken('api-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson('/api/profile/password', [
            'current_password' => 'wrong-password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors('current_password');
    }

    public function test_registration_requires_valid_data(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => '',
            'email' => 'invalid-email',
            'password' => '123',
            'password_confirmation' => '456',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    public function test_login_requires_valid_data(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => '',
            'password' => '',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_profile_update_requires_unique_email(): void
    {
        $user1 = User::factory()->create(['email' => 'user1@example.com']);
        $user2 = User::factory()->create(['email' => 'user2@example.com']);
        $token = $user1->createToken('api-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson('/api/profile', [
            'name' => 'João Silva',
            'email' => 'user2@example.com',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['email']);
    }

    public function test_protected_routes_require_authentication(): void
    {
        $response = $this->getJson('/api/user');
        $response->assertStatus(401);

        $response = $this->postJson('/api/logout');
        $response->assertStatus(401);

        $response = $this->putJson('/api/profile', []);
        $response->assertStatus(401);

        $response = $this->putJson('/api/profile/password', []);
        $response->assertStatus(401);
    }
}
