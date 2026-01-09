<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\NewFollowerNotification;
use App\Notifications\NewLikeNotification;
use App\Jobs\SendFollowNotificationJob;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Database\QueryException;
use Exception;

class NotificationService
{
    /**
     * Send a follow notification with retry logic
     *
     * @param User $follower The user who is following
     * @param User $followed The user being followed
     * @return void
     */
    public function sendFollowNotification(User $follower, User $followed)
    {
        // Don't notify if following yourself
        if ($follower->id === $followed->id) {
            return;
        }

        // Use cache lock to prevent duplicate notifications
        $lockKey = "follow_notification_lock_{$follower->id}_{$followed->id}";
        $lock = Cache::lock($lockKey, 30); // 30 second lock

        if (!$lock->get()) {
            Log::warning('Follow notification already in progress', [
                'follower_id' => $follower->id,
                'followed_id' => $followed->id
            ]);
            return;
        }

        try {
            // Dispatch the notification job asynchronously to prevent blocking the response
            SendFollowNotificationJob::dispatch($follower->id, $followed->id);

            Log::info('Follow notification job dispatched successfully', [
                'follower_id' => $follower->id,
                'followed_id' => $followed->id
            ]);

        } catch (QueryException $e) {
            // Specific handling for database lock timeouts
            Log::error('Failed to dispatch follow notification job (DB lock)', [
                'follower_id'    => $follower->id,
                'followed_id'    => $followed->id,
                'sql_error_code' => $e->getCode(),
                'error'          => $e->getMessage(),
            ]);

            // Release potential stale locks on the jobs table and try again synchronously
            $this->releaseJobTableLocks();

            try {
                $this->sendNotificationSync($follower, $followed);
                Log::info('Follow notification sent synchronously after lock timeout', [
                    'follower_id' => $follower->id,
                    'followed_id' => $followed->id,
                ]);
            } catch (Exception $syncError) {
                Log::error('Synchronous follow notification also failed', [
                    'follower_id'   => $follower->id,
                    'followed_id'   => $followed->id,
                    'sync_error'    => $syncError->getMessage(),
                ]);
            }

        } catch (Exception $e) {
            // Generic error handling (non-DB related)
            Log::error('Failed to dispatch follow notification job', [
                'follower_id' => $follower->id,
                'followed_id' => $followed->id,
                'error'       => $e->getMessage(),
            ]);

            // Fallback to immediate (queued) notification with retry logic
            try {
                $this->executeNotificationWithRetry($follower, $followed, 'follow');
            } catch (Exception $fallbackError) {
                Log::error('Follow notification fallback also failed', [
                    'follower_id'     => $follower->id,
                    'followed_id'     => $followed->id,
                    'original_error'  => $e->getMessage(),
                    'fallback_error'  => $fallbackError->getMessage(),
                ]);
            }
        } finally {
            $lock->release();
        }
    }
    
    /**
     * Send a like notification with retry logic
     *
     * @param User $liker The user who liked the content
     * @param mixed $post The post/content that was liked
     * @param string $reactionType The type of reaction (heart, thumbsup, fire, etc.)
     * @return void
     */
    public function sendLikeNotification(User $liker, $post, $reactionType = 'heart')
    {
        // Get the user who owns the post
        $postOwner = User::find($post->user_id);
        
        // Don't notify if liking your own post
        if ($postOwner->id === $liker->id) {
            return;
        }

        // Use cache lock to prevent duplicate notifications
        $lockKey = "like_notification_lock_{$liker->id}_{$post->id}";
        $lock = Cache::lock($lockKey, 30);

        if (!$lock->get()) {
            Log::warning('Like notification already in progress', [
                'liker_id' => $liker->id,
                'post_id' => $post->id
            ]);
            return;
        }

        try {
            $this->executeLikeNotificationWithRetry($liker, $post, $reactionType);
        } catch (Exception $e) {
            Log::error('Failed to send like notification after retries', [
                'liker_id' => $liker->id,
                'post_id' => $post->id,
                'error' => $e->getMessage()
            ]);
        } finally {
            $lock->release();
        }
    }

    /**
     * Execute notification with retry logic (fallback method)
     */
    private function executeNotificationWithRetry(User $follower, User $followed, string $type): void
    {
        $maxRetries = 3;
        $retryCount = 0;
        $lastException = null;

        while ($retryCount < $maxRetries) {
            try {
                Log::info("Sending {$type} notification attempt", [
                    'attempt' => $retryCount + 1,
                    'follower_id' => $follower->id,
                    'followed_id' => $followed->id
                ]);

                // Using Laravel's built-in notification system
                $followed->notify(new NewFollowerNotification($follower));

                Log::info("{$type} notification sent successfully", [
                    'follower_id' => $follower->id,
                    'followed_id' => $followed->id
                ]);

                return; // Success, exit the retry loop

            } catch (Exception $e) {
                $lastException = $e;
                $retryCount++;

                Log::warning("{$type} notification attempt failed", [
                    'attempt' => $retryCount,
                    'max_retries' => $maxRetries,
                    'follower_id' => $follower->id,
                    'followed_id' => $followed->id,
                    'error' => $e->getMessage()
                ]);

                // Check if it's a retryable error
                if ($this->isRetryableNotificationError($e) && $retryCount < $maxRetries) {
                    // Exponential backoff with jitter
                    $waitTime = (pow(2, $retryCount) + rand(0, 1000)) * 1000; // microseconds
                    usleep($waitTime);
                    continue;
                }

                // If it's not retryable or we're out of retries, break
                break;
            }
        }

        // All retries failed
        Log::error("All {$type} notification attempts failed", [
            'follower_id' => $follower->id,
            'followed_id' => $followed->id,
            'retry_count' => $retryCount,
            'final_error' => $lastException ? $lastException->getMessage() : 'Unknown error'
        ]);

        throw new Exception("Failed to send {$type} notification after {$maxRetries} attempts");
    }

    /**
     * Execute like notification with retry logic
     */
    private function executeLikeNotificationWithRetry(User $liker, $post, string $reactionType): void
    {
        $maxRetries = 3;
        $retryCount = 0;
        $lastException = null;

        while ($retryCount < $maxRetries) {
            try {
                Log::info("Sending like notification attempt", [
                    'attempt' => $retryCount + 1,
                    'liker_id' => $liker->id,
                    'post_id' => $post->id
                ]);

                // Get the user who owns the post
                $postOwner = User::find($post->user_id);
                
                // Using Laravel's built-in notification system
                $postOwner->notify(new NewLikeNotification($liker, $post, $reactionType));

                Log::info("Like notification sent successfully", [
                    'liker_id' => $liker->id,
                    'post_id' => $post->id
                ]);

                return; // Success, exit the retry loop

            } catch (Exception $e) {
                $lastException = $e;
                $retryCount++;

                Log::warning("Like notification attempt failed", [
                    'attempt' => $retryCount,
                    'max_retries' => $maxRetries,
                    'liker_id' => $liker->id,
                    'post_id' => $post->id,
                    'error' => $e->getMessage()
                ]);

                // Check if it's a retryable error
                if ($this->isRetryableNotificationError($e) && $retryCount < $maxRetries) {
                    // Exponential backoff with jitter
                    $waitTime = (pow(2, $retryCount) + rand(0, 1000)) * 1000; // microseconds
                    usleep($waitTime);
                    continue;
                }

                // If it's not retryable or we're out of retries, break
                break;
            }
        }

        // All retries failed
        Log::error("All like notification attempts failed", [
            'liker_id' => $liker->id,
            'post_id' => $post->id,
            'retry_count' => $retryCount,
            'final_error' => $lastException ? $lastException->getMessage() : 'Unknown error'
        ]);

        throw new Exception("Failed to send like notification after {$maxRetries} attempts");
    }

    /**
     * Check if a notification error is retryable
     */
    private function isRetryableNotificationError(Exception $e): bool
    {
        $message = $e->getMessage();
        $code = $e->getCode();

        // Check for lock timeout, deadlock, connection issues, or queue-related errors
        return (
            strpos($message, 'Lock wait timeout exceeded') !== false ||
            strpos($message, 'Deadlock') !== false ||
            strpos($message, 'Connection') !== false ||
            strpos($message, 'Queue') !== false ||
            strpos($message, 'Job') !== false ||
            $code === 1205 || // Lock wait timeout
            $code === 1213 || // Deadlock
            $code === 2006    // MySQL server has gone away
        );
    }

    /**
     * Remove stale reserved jobs older than five minutes to free row locks.
     */
    private function releaseJobTableLocks(): void
    {
        try {
            DB::table('jobs')
                ->whereNotNull('reserved_at')
                ->where('reserved_at', '<', now()->subMinutes(5)->timestamp)
                ->delete();

            Log::warning('Released stale job table locks by purging old reserved jobs');
        } catch (Exception $e) {
            Log::error('Failed to release job table locks', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send notification synchronously (no queue) to guarantee delivery when the queue is locked.
     */
    private function sendNotificationSync(User $follower, User $followed): void
    {
        Notification::sendNow(
            $followed,
            (new NewFollowerNotification($follower))->onConnection('sync')
        );
    }
}