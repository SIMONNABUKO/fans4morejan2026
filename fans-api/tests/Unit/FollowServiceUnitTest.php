<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\FollowService;
use App\Services\EmailService;
use App\Services\SettingsService;
use App\Services\ListService;
use App\Services\NotificationService;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Exception;
use Mockery;

class FollowServiceUnitTest extends TestCase
{
    protected FollowService $followService;
    protected $emailService;
    protected $settingsService;
    protected $listService;
    protected $notificationService;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Mock all dependencies
        $this->emailService = Mockery::mock(EmailService::class);
        $this->settingsService = Mockery::mock(SettingsService::class);
        $this->listService = Mockery::mock(ListService::class);
        $this->notificationService = Mockery::mock(NotificationService::class);

        $this->followService = new FollowService(
            $this->emailService,
            $this->settingsService,
            $this->listService,
            $this->notificationService
        );

        // Clear cache
        Cache::flush();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_prevents_following_yourself()
    {
        $user = new User();
        $user->id = 1;
        $user->role = 'creator';
        $user->can_be_followed = true;

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Users cannot follow themselves.');

        $this->followService->follow($user, $user);
    }

    /** @test */
    public function it_prevents_following_non_creators()
    {
        $follower = new User();
        $follower->id = 1;
        $follower->role = 'user';

        $followed = new User();
        $followed->id = 2;
        $followed->role = 'user';
        $followed->can_be_followed = true;

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Only creators can be followed.');

        $this->followService->follow($follower, $followed);
    }

    /** @test */
    public function it_prevents_following_users_who_cannot_be_followed()
    {
        $follower = new User();
        $follower->id = 1;
        $follower->role = 'user';

        $followed = new User();
        $followed->id = 2;
        $followed->role = 'creator';
        $followed->can_be_followed = false;

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('This user cannot be followed.');

        $this->followService->follow($follower, $followed);
    }

    /** @test */
    public function it_uses_cache_locks_to_prevent_duplicate_operations()
    {
        $follower = new User();
        $follower->id = 1;
        $follower->role = 'user';

        $followed = new User();
        $followed->id = 2;
        $followed->role = 'creator';
        $followed->can_be_followed = true;

        // Manually acquire the lock
        $lockKey = "follow_lock_{$follower->id}_{$followed->id}";
        $lock = Cache::lock($lockKey, 10);
        $lock->get();

        // Try to follow while lock is held
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Follow operation already in progress. Please try again.');

        $this->followService->follow($follower, $followed);

        $lock->release();
    }

    /** @test */
    public function it_classifies_retryable_errors_correctly()
    {
        $follower = new User();
        $follower->id = 1;
        $follower->role = 'user';

        $followed = new User();
        $followed->id = 2;
        $followed->role = 'creator';
        $followed->can_be_followed = true;

        // Test lock timeout error
        $lockTimeoutException = new Exception('Lock wait timeout exceeded');
        $this->assertTrue($this->invokePrivateMethod($this->followService, 'isRetryableError', [$lockTimeoutException]));

        // Test deadlock error
        $deadlockException = new Exception('Deadlock found when trying to get lock');
        $this->assertTrue($this->invokePrivateMethod($this->followService, 'isRetryableError', [$deadlockException]));

        // Test connection error
        $connectionException = new Exception('Connection lost');
        $this->assertTrue($this->invokePrivateMethod($this->followService, 'isRetryableError', [$connectionException]));

        // Test non-retryable error
        $nonRetryableException = new Exception('User not found');
        $this->assertFalse($this->invokePrivateMethod($this->followService, 'isRetryableError', [$nonRetryableException]));
    }

    /** @test */
    public function it_handles_validation_errors_properly()
    {
        $follower = new User();
        $follower->id = 1;
        $follower->role = 'user';

        $followed = new User();
        $followed->id = 2;
        $followed->role = 'user'; // This should cause validation to fail
        $followed->can_be_followed = true;

        // Test validation method directly - should throw exception for non-creator
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Only creators can be followed.');
        $this->invokePrivateMethod($this->followService, 'validateFollowRequest', [$follower, $followed]);
    }

    /**
     * Helper method to invoke private methods for testing
     */
    private function invokePrivateMethod($object, $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);
        return $method->invokeArgs($object, $parameters);
    }
}
