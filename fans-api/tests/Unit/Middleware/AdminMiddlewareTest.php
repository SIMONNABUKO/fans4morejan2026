<?php

namespace Tests\Unit\Middleware;

use App\Http\Middleware\AdminMiddleware;
use App\Models\User;
use Illuminate\Http\Request;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    private AdminMiddleware $middleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->middleware = new AdminMiddleware();
    }

    public function test_allows_admin_user(): void
    {
        /** @var User $user */
        $user = User::factory()->create(['role' => 'admin']);
        $this->actingAs($user);

        $request = Request::create('/api/admin/test', 'GET');
        $response = $this->middleware->handle($request, function ($req) {
            return response()->json(['success' => true]);
        });

        $this->assertEquals(200, $response->status());
    }

    public function test_blocks_non_admin_user(): void
    {
        /** @var User $user */
        $user = User::factory()->create(['role' => 'user']);
        $this->actingAs($user);

        $request = Request::create('/api/admin/test', 'GET');
        $response = $this->middleware->handle($request, function ($req) {
            return response()->json(['success' => true]);
        });

        $this->assertEquals(403, $response->status());
        $this->assertEquals('Unauthorized. Admin access required.', json_decode($response->getContent())->message);
    }

    public function test_blocks_unauthenticated_user(): void
    {
        $request = Request::create('/api/admin/test', 'GET');
        $response = $this->middleware->handle($request, function ($req) {
            return response()->json(['success' => true]);
        });

        $this->assertEquals(403, $response->status());
        $this->assertEquals('Unauthorized. Admin access required.', json_decode($response->getContent())->message);
    }
} 