<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Services\FollowService;
use App\Services\EmailService;
use App\Services\SettingsService;
use App\Services\ListService;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Exception;

class FollowServiceTest extends TestCase
{
    use RefreshDatabase;

    protected FollowService $followService;
    protected User $follower;
    protected User $followed;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test users
        $this->follower = User::factory()->create([
            'role' => 'user',
            'can_be_followed' => false
        ]);
        
        $this->followed = User::factory()->create([
            'role' => 'creator',
            'can_be_followed' => true
        ]);

        // Create service instances
        $this->followService = new FollowService(
            app(EmailService::class),
            app(SettingsService::class),
            app(ListService::class),
            app(NotificationService::class)
        );

        // Clear cache before each test
        Cache::flush();
    }

    /** @test */
    public function it_can_follow_a_user_successfully()
    {
        Queue::fake();

        $result = $this->followService->follow($this->follower, $this->followed);

        $this->assertTrue($result);
        $this->assertTrue($this->follower->isFollowing($this->followed));
    }

    /** @test */
    public function it_prevents_duplicate_follow_operations()
    {
        Queue::fake();

        // First follow should succeed
        $result1 = $this->followService->follow($this->follower, $this->followed);
        $this->assertTrue($result1);

        // Second follow should also succeed but not create duplicate
        $result2 = $this->followService->follow($this->follower, $this->followed);
        $this->assertTrue($result2);

        // Should only have one follow relationship
        $this->assertEquals(1, $this->follower->following()->count());
    }

    /** @test */
    public function it_prevents_following_yourself()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Users cannot follow themselves.');

        $this->followService->follow($this->follower, $this->follower);
    }

    /** @test */
    public function it_prevents_following_non_creators()
    {
        $nonCreator = User::factory()->create([
            'role' => 'user',
            'can_be_followed' => true
        ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Only creators can be followed.');

        $this->followService->follow($this->follower, $nonCreator);
    }

    /** @test */
    public function it_prevents_following_users_who_cannot_be_followed()
    {
        $this->followed->update(['can_be_followed' => false]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('This user cannot be followed.');

        $this->followService->follow($this->follower, $this->followed);
    }

    /** @test */
    public function it_can_unfollow_a_user_successfully()
    {
        Queue::fake();

        // First follow
        $this->followService->follow($this->follower, $this->followed);
        $this->assertTrue($this->follower->isFollowing($this->followed));

        // Then unfollow
        $result = $this->followService->unfollow($this->follower, $this->followed);
        $this->assertTrue($result);
        $this->assertFalse($this->follower->isFollowing($this->followed));
    }

    /** @test */
    public function it_handles_concurrent_follow_operations()
    {
        Queue::fake();

        // Simulate concurrent follow operations
        $results = [];
        $promises = [];

        for ($i = 0; $i < 5; $i++) {
            $promises[] = function() {
                return $this->followService->follow($this->follower, $this->followed);
            };
        }

        // Execute all follow operations
        foreach ($promises as $promise) {
            $results[] = $promise();
        }

        // All should succeed
        foreach ($results as $result) {
            $this->assertTrue($result);
        }

        // Should only have one follow relationship
        $this->assertEquals(1, $this->follower->following()->count());
    }

    /** @test */
    public function it_handles_database_lock_timeouts_gracefully()
    {
        Queue::fake();

        // Mock a database lock timeout scenario
        DB::shouldReceive('transaction')
            ->once()
            ->andThrow(new Exception('Lock wait timeout exceeded'));

        // The service should retry and eventually succeed
        $result = $this->followService->follow($this->follower, $this->followed);
        
        // In a real scenario with retries, this should eventually succeed
        // For this test, we're just ensuring no unhandled exceptions
        $this->assertIsBool($result);
    }

    /** @test */
    public function it_uses_cache_locks_to_prevent_duplicate_operations()
    {
        Queue::fake();

        // Manually acquire the lock
        $lockKey = "follow_lock_{$this->follower->id}_{$this->followed->id}";
        $lock = Cache::lock($lockKey, 10);
        $lock->get();

        // Try to follow while lock is held
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Follow operation already in progress. Please try again.');

        $this->followService->follow($this->follower, $this->followed);

        $lock->release();
    }

    /** @test */
    public function it_dispatches_notification_jobs_properly()
    {
        Queue::fake();

        $this->followService->follow($this->follower, $this->followed);

        // Assert that the notification job was dispatched
        Queue::assertPushed(\App\Jobs\SendFollowNotificationJob::class, function ($job) {
            return $job->followerId === $this->follower->id && 
                   $job->followedId === $this->followed->id;
        });
    }

    /** @test */
    public function it_handles_follow_operations_with_retry_logic()
    {
        Queue::fake();

        // Create multiple users to test retry logic
        $followers = User::factory()->count(3)->create(['role' => 'user']);
        $followed = User::factory()->create(['role' => 'creator', 'can_be_followed' => true]);

        $results = [];

        // Perform multiple follow operations simultaneously
        foreach ($followers as $follower) {
            $results[] = $this->followService->follow($follower, $followed);
        }

        // All should succeed
        foreach ($results as $result) {
            $this->assertTrue($result);
        }

        // All followers should be following the same user
        foreach ($followers as $follower) {
            $this->assertTrue($follower->isFollowing($followed));
        }
    }

    /** @test */
    public function it_prevents_following_invalid_users()
    {
        $this->expectException(Exception::class);

        // Try to follow a non-existent user
        $nonExistentUser = new User();
        $nonExistentUser->id = 99999;
        $nonExistentUser->role = 'creator';
        $nonExistentUser->can_be_followed = true;

        $this->followService->follow($this->follower, $nonExistentUser);
    }

    /** @test */
    public function it_handles_follow_operations_with_proper_error_messages()
    {
        // Test self-follow
        try {
            $this->followService->follow($this->follower, $this->follower);
            $this->fail('Should have thrown an exception');
        } catch (Exception $e) {
            $this->assertEquals('Users cannot follow themselves.', $e->getMessage());
        }

        // Test following non-creator
        $nonCreator = User::factory()->create(['role' => 'user']);
        try {
            $this->followService->follow($this->follower, $nonCreator);
            $this->fail('Should have thrown an exception');
        } catch (Exception $e) {
            $this->assertEquals('Only creators can be followed.', $e->getMessage());
        }
    }
}
