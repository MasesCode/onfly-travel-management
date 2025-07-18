<?php

namespace Tests\Feature\Middleware;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_requests_are_rejected(): void
    {
        $protectedRoutes = [
            'GET /api/user',
            'POST /api/logout',
            'PUT /api/profile',
            'PUT /api/profile/password',
            'GET /api/orders',
            'POST /api/orders',
            'GET /api/orders/1',
            'PUT /api/orders/1',
            'DELETE /api/orders/1',
            'PATCH /api/orders/1/status',
            'GET /api/order-statuses',
            'POST /api/order-statuses',
            'DELETE /api/order-statuses/1',
            'GET /api/users',
            'POST /api/users',
            'PUT /api/users/1',
            'DELETE /api/users/1',
        ];

        foreach ($protectedRoutes as $route) {
            [$method, $uri] = explode(' ', $route);

            $response = $this->json($method, $uri);

            $response->assertStatus(401);
        }
    }

    public function test_authenticated_requests_with_valid_token_are_accepted(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/user');

        $response->assertStatus(200);
    }

    public function test_authenticated_requests_with_invalid_token_are_rejected(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer invalid-token',
        ])->getJson('/api/user');

        $response->assertStatus(401);
    }

    public function test_authenticated_requests_without_authorization_header_are_rejected(): void
    {
        $response = $this->getJson('/api/user');

        $response->assertStatus(401);
    }

    public function test_token_with_different_abilities_still_works(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token', ['read', 'write'])->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/user');

        $response->assertStatus(200);
    }

    public function test_public_routes_do_not_require_authentication(): void
    {
        $publicRoutes = [
            'POST /api/login',
            'POST /api/register',
        ];

        foreach ($publicRoutes as $route) {
            [$method, $uri] = explode(' ', $route);

            $response = $this->json($method, $uri, [
                'email' => 'test@example.com',
                'password' => 'password',
            ]);

            $response->assertStatus(422);
        }
    }
}
