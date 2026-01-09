<?php

namespace App\Jobs;

use App\Models\User;
use App\Notifications\NewFollowerNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Exception;

class SendFollowNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3; // Maximum number of attempts
    public $timeout = 60; // Job timeout in seconds
    public $backoff = [2, 10, 30]; // Retry delays in seconds

    protected $followerId;
    protected $followedId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $followerId, int $followedId)
    {
        $this->followerId = $followerId;
        $this->followedId = $followedId;
        
        // Set queue name for better organization
        $this->onQueue('notifications');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::info('Starting follow notification job', [
                'follower_id' => $this->followerId,
                'followed_id' => $this->followedId,
                'attempt' => $this->attempts()
            ]);

            // Load users with proper error handling
            $follower = User::find($this->followerId);
            $followed = User::find($this->followedId);

            if (!$follower || !$followed) {
                Log::warning('User not found for follow notification', [
                    'follower_id' => $this->followerId,
                    'followed_id' => $this->followedId,
                    'follower_exists' => (bool) $follower,
                    'followed_exists' => (bool) $followed
                ]);
                return;
            }

            // Don't notify if following yourself
            if ($follower->id === $followed->id) {
                Log::info('Skipping self-follow notification', [
                    'user_id' => $follower->id
                ]);
                return;
            }

            // Use cache lock to prevent duplicate notifications
            $lockKey = "follow_notification_job_lock_{$this->followerId}_{$this->followedId}";
            $lock = Cache::lock($lockKey, 60); // 60 second lock

            if (!$lock->get()) {
                Log::warning('Follow notification job already in progress', [
                    'follower_id' => $this->followerId,
                    'followed_id' => $this->followedId
                ]);
                return;
            }

            try {
                // Send the notification
                $followed->notify(new NewFollowerNotification($follower));

                Log::info('Follow notification job completed successfully', [
                    'follower_id' => $this->followerId,
                    'followed_id' => $this->followedId
                ]);

            } finally {
                $lock->release();
            }

        } catch (Exception $e) {
            Log::error('Follow notification job failed', [
                'follower_id' => $this->followerId,
                'followed_id' => $this->followedId,
                'attempt' => $this->attempts(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Re-throw the exception to trigger retry
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(Exception $exception): void
    {
        Log::error('Follow notification job failed permanently', [
            'follower_id' => $this->followerId,
            'followed_id' => $this->followedId,
            'final_error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);

        // You could send an alert here or log to a monitoring service
        // For now, we'll just log the failure
    }

    /**
     * Calculate the number of seconds to wait before retrying the job.
     */
    public function retryAfter(): int
    {
        return $this->backoff[$this->attempts() - 1] ?? 30;
    }

    /**
     * Determine the time at which the job should timeout.
     */
    public function retryUntil(): \DateTime
    {
        return now()->addMinutes(10); // Don't retry after 10 minutes
    }
}
