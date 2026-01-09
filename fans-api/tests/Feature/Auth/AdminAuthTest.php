<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAuthTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;
    private User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Create an admin user
        $this->adminUser = User::factory()->create([
            'email' => 'admin@example.com',
            'role' => 'admin'
        ]);

        // Create a regular user
        $this->regularUser = User::factory()->create([
            'email' => 'user@example.com',
            'role' => 'user'
        ]);
    }

    public function test_admin_can_login(): void
    {
        $response = $this->postJson('/api/admin/login', [
            'login' => 'admin@example.com',
            'password' => 'password'
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'user',
                'token'
            ]);
    }

    public function test_regular_user_cannot_login_to_admin(): void
    {
        $response = $this->postJson('/api/admin/login', [
            'login' => 'user@example.com',
            'password' => 'password'
        ]);

        $response->assertStatus(403);
    }

    public function test_admin_can_logout(): void
    {
        // Login first
        $token = $this->adminUser->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/admin/logout');

        $response->assertStatus(200);
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_admin_middleware_blocks_non_admin_users(): void
    {
        // Create a token for regular user
        $token = $this->regularUser->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/admin/logout');

        $response->assertStatus(403);
    }

    public function test_invalid_login_credentials(): void
    {
        $response = $this->postJson('/api/admin/login', [
            'login' => 'admin@example.com',
            'password' => 'wrong-password'
        ]);

        $response->assertStatus(401);
    }

    public function test_login_validation(): void
    {
        $response = $this->postJson('/api/admin/login', [
            'login' => '',
            'password' => ''
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['login', 'password']);
    }
} 