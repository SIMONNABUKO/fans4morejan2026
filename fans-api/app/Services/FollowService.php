<?php

namespace App\Services;

use App\Models\User;
use App\Models\AutomatedMessage;
use App\Jobs\SendAutomatedMessage;
use Illuminate\Support\Facades\DB;
use App\Services\EmailService;
use App\Services\SettingsService;
use App\Services\ListService;
use App\Services\NotificationService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Exception;

class FollowService
{
    protected $emailService;
    protected $settingsService;
    protected $listService;
    protected $notificationService;

    public function __construct(
        EmailService $emailService,
        SettingsService $settingsService,
        ListService $listService,
        NotificationService $notificationService
    ) {
        $this->emailService = $emailService;
        $this->settingsService = $settingsService;
        $this->listService = $listService;
        $this->notificationService = $notificationService;
    }

    public function follow(User $follower, User $followed)
    {
        Log::info('Follow attempt', ['follower_id' => $follower->id, 'followed_id' => $followed->id]);

        // Validation checks
        $this->validateFollowRequest($follower, $followed);

        // Use cache lock to prevent duplicate follow operations
        $lockKey = "follow_lock_{$follower->id}_{$followed->id}";
        $lock = Cache::lock($lockKey, 10); // 10 second lock

        if (!$lock->get()) {
            Log::warning('Follow operation already in progress', [
                'follower_id' => $follower->id, 
                'followed_id' => $followed->id
            ]);
            throw new Exception('Follow operation already in progress. Please try again.');
        }

        try {
            $result = $this->executeFollowWithRetry($follower, $followed);
            
            // Handle post-follow operations asynchronously to prevent blocking the response
            if ($result['isNowFollowing'] && !$result['wasFollowing']) {
                dispatch(function () use ($follower, $followed) {
                    $this->handlePostFollowOperations($follower, $followed);
                })->afterResponse();
            }

            return $result['isNowFollowing'];
        } finally {
            $lock->release();
        }
    }

    public function unfollow(User $follower, User $followed)
    {
        Log::info('Unfollow attempt', ['follower_id' => $follower->id, 'followed_id' => $followed->id]);

        if ($follower->id === $followed->id) {
            throw new Exception('Users cannot unfollow themselves.');
        }

        // Use cache lock to prevent duplicate unfollow operations
        $lockKey = "unfollow_lock_{$follower->id}_{$followed->id}";
        $lock = Cache::lock($lockKey, 10);

        if (!$lock->get()) {
            Log::warning('Unfollow operation already in progress', [
                'follower_id' => $follower->id, 
                'followed_id' => $followed->id
            ]);
            throw new Exception('Unfollow operation already in progress. Please try again.');
        }

        try {
            $result = $this->executeUnfollowWithRetry($follower, $followed);
            
            // Handle post-unfollow operations asynchronously to prevent blocking the response
            if (!$result['isNowFollowing'] && $result['wasFollowing']) {
                dispatch(function () use ($follower, $followed) {
                    $this->handlePostUnfollowOperations($follower, $followed);
                })->afterResponse();
            }

            return !$result['isNowFollowing'];
        } finally {
            $lock->release();
        }
    }

    public function getFollowers(User $user, $perPage = 15): LengthAwarePaginator
    {
        return $user->followers()
            ->withPivot('created_at')
            ->orderByPivot('created_at', 'desc')
            ->paginate($perPage);
    }

    public function getFollowing(User $user, $perPage = 15): LengthAwarePaginator
    {
        return $user->following()
            ->withPivot('created_at')
            ->orderByPivot('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Validate follow request
     */
    private function validateFollowRequest(User $follower, User $followed): void
    {
        if ($follower->id === $followed->id) {
            Log::warning('User attempted to follow themselves', ['user_id' => $follower->id]);
            throw new Exception('Users cannot follow themselves.');
        }

        if (!$followed->can_be_followed) {
            Log::warning('User attempted to follow a user that cannot be followed', [
                'follower_id' => $follower->id, 
                'followed_id' => $followed->id
            ]);
            throw new Exception('This user cannot be followed.');
        }

        if ($followed->role !== 'creator') {
            Log::warning('User attempted to follow a non-creator user', [
                'follower_id' => $follower->id, 
                'followed_id' => $followed->id, 
                'role' => $followed->role
            ]);
            throw new Exception('Only creators can be followed.');
        }
    }

    /**
     * Execute follow operation with retry logic
     */
    private function executeFollowWithRetry(User $follower, User $followed): array
    {
        $maxRetries = 3;
        $retryCount = 0;
        $lastException = null;

        while ($retryCount < $maxRetries) {
            try {
                return DB::transaction(function () use ($follower, $followed) {
                    // Check current follow status
                    $wasFollowing = $follower->isFollowing($followed);
                    
                    if ($wasFollowing) {
                        Log::info('User already following', [
                            'follower_id' => $follower->id, 
                            'followed_id' => $followed->id
                        ]);
                        return [
                            'wasFollowing' => true,
                            'isNowFollowing' => true
                        ];
                    }

                    // Perform the follow operation
                    $follower->following()->syncWithoutDetaching([$followed->id]);
                    
                    // Verify the follow was successful
                    $isNowFollowing = $follower->isFollowing($followed);
                    
                    Log::info('Follow operation completed', [
                        'follower_id' => $follower->id,
                        'followed_id' => $followed->id,
                        'isNowFollowing' => $isNowFollowing
                    ]);

                    return [
                        'wasFollowing' => false,
                        'isNowFollowing' => $isNowFollowing
                    ];
                }, 5); // 5 retries for deadlock

            } catch (Exception $e) {
                $lastException = $e;
                $retryCount++;

                Log::warning('Follow operation attempt failed', [
                    'follower_id' => $follower->id,
                    'followed_id' => $followed->id,
                    'retry_count' => $retryCount,
                    'max_retries' => $maxRetries,
                    'error' => $e->getMessage()
                ]);

                // Check if it's a lock timeout or deadlock
                if ($this->isRetryableError($e) && $retryCount < $maxRetries) {
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
        Log::error('All follow operation attempts failed', [
            'follower_id' => $follower->id,
            'followed_id' => $followed->id,
            'retry_count' => $retryCount,
            'final_error' => $lastException ? $lastException->getMessage() : 'Unknown error'
        ]);

        throw new Exception('Follow operation failed after multiple attempts. Please try again.');
    }

    /**
     * Execute unfollow operation with retry logic
     */
    private function executeUnfollowWithRetry(User $follower, User $followed): array
    {
        $maxRetries = 3;
        $retryCount = 0;
        $lastException = null;

        while ($retryCount < $maxRetries) {
            try {
                return DB::transaction(function () use ($follower, $followed) {
                    // Check current follow status
                    $wasFollowing = $follower->isFollowing($followed);
                    
                    if (!$wasFollowing) {
                        Log::info('User not following', [
                            'follower_id' => $follower->id, 
                            'followed_id' => $followed->id
                        ]);
                        return [
                            'wasFollowing' => false,
                            'isNowFollowing' => false
                        ];
                    }

                    // Perform the unfollow operation
                    $follower->following()->detach($followed->id);
                    
                    // Verify the unfollow was successful
                    $isNowFollowing = $follower->isFollowing($followed);
                    
                    Log::info('Unfollow operation completed', [
                        'follower_id' => $follower->id,
                        'followed_id' => $followed->id,
                        'isNowFollowing' => $isNowFollowing
                    ]);

                    return [
                        'wasFollowing' => true,
                        'isNowFollowing' => $isNowFollowing
                    ];
                }, 5); // 5 retries for deadlock

            } catch (Exception $e) {
                $lastException = $e;
                $retryCount++;

                Log::warning('Unfollow operation attempt failed', [
                    'follower_id' => $follower->id,
                    'followed_id' => $followed->id,
                    'retry_count' => $retryCount,
                    'max_retries' => $maxRetries,
                    'error' => $e->getMessage()
                ]);

                // Check if it's a lock timeout or deadlock
                if ($this->isRetryableError($e) && $retryCount < $maxRetries) {
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
        Log::error('All unfollow operation attempts failed', [
            'follower_id' => $follower->id,
            'followed_id' => $followed->id,
            'retry_count' => $retryCount,
            'final_error' => $lastException ? $lastException->getMessage() : 'Unknown error'
        ]);

        throw new Exception('Unfollow operation failed after multiple attempts. Please try again.');
    }

    /**
     * Check if an error is retryable
     */
    private function isRetryableError(Exception $e): bool
    {
        $message = $e->getMessage();
        $code = $e->getCode();

        // Check for lock timeout, deadlock, or connection issues
        return (
            strpos($message, 'Lock wait timeout exceeded') !== false ||
            strpos($message, 'Deadlock') !== false ||
            strpos($message, 'Connection') !== false ||
            $code === 1205 || // Lock wait timeout
            $code === 1213 || // Deadlock
            $code === 2006    // MySQL server has gone away
        );
    }

    /**
     * Handle post-follow operations (outside of any transaction)
     */
    private function handlePostFollowOperations(User $follower, User $followed): void
    {
        try {
            Log::info('Starting post-follow operations', [
                'follower_id' => $follower->id,
                'followed_id' => $followed->id
            ]);

            // Update follow lists
            $this->updateFollowLists($follower, $followed);

            // Send notifications (this will queue jobs)
            $this->sendFollowNotification($follower, $followed);

            // Handle automated messages
            $this->handleAutomatedMessage($follower, $followed);

            Log::info('Post-follow operations completed successfully', [
                'follower_id' => $follower->id,
                'followed_id' => $followed->id
            ]);

        } catch (Exception $e) {
            // Log error but don't fail the follow operation
            Log::error('Error in post-follow operations', [
                'follower_id' => $follower->id,
                'followed_id' => $followed->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Handle post-unfollow operations
     */
    private function handlePostUnfollowOperations(User $follower, User $followed): void
    {
        try {
            Log::info('Starting post-unfollow operations', [
                'follower_id' => $follower->id,
                'followed_id' => $followed->id
            ]);

            $this->updateUnfollowLists($follower, $followed);

            Log::info('Post-unfollow operations completed successfully', [
                'follower_id' => $follower->id,
                'followed_id' => $followed->id
            ]);

        } catch (Exception $e) {
            Log::error('Error in post-unfollow operations', [
                'follower_id' => $follower->id,
                'followed_id' => $followed->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    private function sendFollowNotification(User $follower, User $followed)
    {
        try {
            // Send the database/broadcast notification for real-time updates
            $this->notificationService->sendFollowNotification($follower, $followed);
            Log::info('Sent real-time follow notification', [
                'follower_id' => $follower->id, 
                'followed_id' => $followed->id
            ]);
            
            // Handle email notifications based on user settings
            $shouldSendEmailNotification = $this->settingsService->getUserSetting(
                $followed, 
                'emailNotifications', 
                'new_followers', 
                true
            );

            if ($shouldSendEmailNotification) {
                Log::info('Queueing new follower email notification', [
                    'follower_id' => $follower->id, 
                    'followed_id' => $followed->id
                ]);
                
                // Dispatch email notification as a job to prevent blocking
                dispatch(function () use ($followed, $follower) {
                    $this->emailService->sendNewFollowerNotification($followed, [
                        'follower_name' => $follower->name,
                        'follower_id' => $follower->id,
                    ]);
                })->afterResponse();
            } else {
                Log::info('New follower email notification not sent due to user settings', [
                    'followed_id' => $followed->id
                ]);
            }
        } catch (Exception $e) {
            Log::error('Error sending follow notification', [
                'follower_id' => $follower->id,
                'followed_id' => $followed->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    private function updateFollowLists(User $follower, User $followed)
    {
        try {
            $this->listService->addToList($follower, 'Following', $followed);
            $this->listService->addToList($followed, 'Followers', $follower);
            Log::info('Updated follow lists', [
                'follower_id' => $follower->id, 
                'followed_id' => $followed->id
            ]);
        } catch (Exception $e) {
            Log::error('Error updating follow lists', [
                'follower_id' => $follower->id,
                'followed_id' => $followed->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    private function updateUnfollowLists(User $follower, User $followed)
    {
        try {
            $this->listService->removeFromList($follower, 'Following', $followed);
            $this->listService->removeFromList($followed, 'Followers', $follower);
            Log::info('Updated unfollow lists', [
                'follower_id' => $follower->id, 
                'followed_id' => $followed->id
            ]);
        } catch (Exception $e) {
            Log::error('Error updating unfollow lists', [
                'follower_id' => $follower->id,
                'followed_id' => $followed->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    private function handleAutomatedMessage(User $follower, User $followed)
    {
        try {
            // Check if user has an active automated message for new followers
            $automatedMessage = AutomatedMessage::where('user_id', $followed->id)
                ->where('trigger', 'new_follower')
                ->where('is_active', true)
                ->first();

            if ($automatedMessage) {
                Log::info('Found active automated message', [
                    'message_id' => $automatedMessage->id,
                    'follower_id' => $follower->id,
                    'followed_id' => $followed->id
                ]);

                // Prepare message data
                $messageData = [
                    'content' => $automatedMessage->content,
                    'automated_message_id' => $automatedMessage->id,
                    'trigger' => 'new_follower'
                ];

                // Add media if exists
                if ($automatedMessage->media && $automatedMessage->media->count() > 0) {
                    $messageData['media'] = $automatedMessage->media->map(function ($media) {
                        return [
                            'file_path' => $media->file_path,
                            'file_name' => $media->file_name,
                            'file_type' => $media->file_type,
                        ];
                    })->toArray();
                }

                Log::info('Dispatching automated follow message', [
                    'follower_id' => $follower->id,
                    'followed_id' => $followed->id,
                    'message_data' => array_diff_key($messageData, ['media' => null])
                ]);

                // Dispatch the job with correct parameters and ensure it doesn't block the response
                SendAutomatedMessage::dispatch(
                    $followed->id, // sender ID (the person being followed)
                    $follower->id, // recipient ID (the follower)
                    $messageData
                )->delay(now()->addSeconds($automatedMessage->sent_delay ?? 0))->afterResponse();
            } else {
                Log::info('No active automated message found for new followers', [
                    'user_id' => $followed->id
                ]);
            }
        } catch (Exception $e) {
            Log::error('Error handling automated follow message', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'follower_id' => $follower->id,
                'followed_id' => $followed->id
            ]);
        }
    }
}
