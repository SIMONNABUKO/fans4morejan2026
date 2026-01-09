<?php

namespace App\Services;

use App\Models\User;
use App\Models\Subscription;
use App\Repositories\PostRepository;
use App\Repositories\UserRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FeedService
{
    protected $postRepository;
    protected $permissionService;
    protected $userRepository;
    protected $listService;
    protected $contentVisibilityService;

    public function __construct(
        PostRepository $postRepository,
        PermissionService $permissionService,
        UserRepository $userRepository,
        ListService $listService,
        ContentVisibilityService $contentVisibilityService
    ) {
        $this->postRepository = $postRepository;
        $this->permissionService = $permissionService;
        $this->userRepository = $userRepository;
        $this->listService = $listService;
        $this->contentVisibilityService = $contentVisibilityService;
    }

    /**
     * Get feed posts for the user (ALL posts from ALL users except blocked/muted)
     *
     * @param User $user The user to get feed for
     * @param int $perPage Number of posts per page
     * @param string|null $userIp User's IP address for location filtering
     * @return array
     */
    public function getFeedPosts(User $user, int $perPage = 15, ?string $userIp = null): array
    {
        // Get ALL creator users in the system (don't filter by location here)
        $allCreatorUsers = User::where('role', 'creator')
            ->where('id', '!=', $user->id) // Exclude current user
            ->pluck('id');

        // Get posts from ALL creators, filtering out blocked/muted users
        $posts = $this->postRepository->getLatestPostsFromUsers(
            $user,
            $allCreatorUsers,
            $perPage
        );

        // Filter posts by location blocking at the database level
        $userLocationService = app(\App\Services\UserLocationService::class);
        $locationService = app(\App\Services\LocationService::class);
        
        // Get user location data - use stored location or detect from IP
        $userLocation = null;
        if ($user->country_code) {
            // User has stored location data
            $userLocation = [
                'country_code' => $user->country_code,
                'country_name' => $user->country_name,
                'region_name' => $user->region_name,
                'city_name' => $user->city_name,
            ];
        } elseif ($userIp) {
            // Try to detect location from IP
            $userLocation = $locationService->getUserLocation($userIp);
        }
        
        // Get the IDs of creators who have blocked the viewer's location
        $blockedCreatorIds = collect();
        
        if ($userLocation && isset($userLocation['country_code'])) {
            $blockedCreatorIds = User::whereIn('id', $allCreatorUsers)
                ->whereHas('blockedLocations', function ($query) use ($userLocation) {
                    $query->where(function ($q) use ($userLocation) {
                        // Check country level
                        $q->where(function ($countryQ) use ($userLocation) {
                            $countryQ->where('location_type', 'country')
                                     ->where('country_code', $userLocation['country_code']);
                        });
                        
                        // Check region level with flexible matching
                        if (isset($userLocation['region_name']) && $userLocation['region_name']) {
                            $q->orWhere(function ($regionQ) use ($userLocation) {
                                $regionQ->where('location_type', 'region')
                                        ->where('country_code', $userLocation['country_code'])
                                        ->where(function ($flexibleQ) use ($userLocation) {
                                            // Exact match
                                            $flexibleQ->where('region_name', $userLocation['region_name'])
                                            // Partial matches (e.g., "Nairobi" in "Nairobi County")
                                            ->orWhere(function ($partialQ) use ($userLocation) {
                                                $partialQ->whereRaw('LOWER(region_name) LIKE ?', ['%' . strtolower($userLocation['region_name']) . '%'])
                                                        ->orWhereRaw('LOWER(?) LIKE CONCAT("%", LOWER(region_name), "%")', [$userLocation['region_name']]);
                                            });
                                        });
                            });
                        }
                        
                        // Check city level with flexible region matching
                        if (isset($userLocation['city_name']) && $userLocation['city_name']) {
                            $q->orWhere(function ($cityQ) use ($userLocation) {
                                $cityQ->where('location_type', 'city')
                                      ->where('country_code', $userLocation['country_code'])
                                      ->where('city_name', $userLocation['city_name'])
                                      ->where(function ($flexibleQ) use ($userLocation) {
                                          // Exact region match
                                          $flexibleQ->where('region_name', $userLocation['region_name'])
                                          // Partial region matches
                                          ->orWhere(function ($partialQ) use ($userLocation) {
                                              $partialQ->whereRaw('LOWER(region_name) LIKE ?', ['%' . strtolower($userLocation['region_name']) . '%'])
                                                      ->orWhereRaw('LOWER(?) LIKE CONCAT("%", LOWER(region_name), "%")', [$userLocation['region_name']]);
                                          });
                                      });
                            });
                        }
                    });
                })
                ->pluck('id');
        }

        // Filter out posts from blocked creators
        $posts->setCollection(
            $posts->getCollection()->filter(function ($post) use ($blockedCreatorIds) {
                return !$blockedCreatorIds->contains($post->user_id);
            })
        );

        $posts->getCollection()->transform(function ($post) use ($user) {
            $post->user_has_permission = $this->permissionService->checkPermission($post, $user);
            $post->required_permissions = $this->permissionService->getRequiredPermissions($post, $user);
            return $post;
        });

        $suggestedUsers = $this->getSuggestedUsers($user, 5);

        return [
            'posts' => $posts,
            'suggested_users' => $suggestedUsers,
        ];
    }


    /**
     * Get feed post previews for a user
     *
     * @param User $user The user to get feed for
     * @param int $perPage Number of posts per page
     * @return array
     */
    public function getFeedPreviews(User $user, int $perPage = 15): array
    {
        $followedAndSubscribedUsers = $user->followedAndSubscribedUsers();

        // Get posts from allowed users, sorted by ID in descending order (newest first)
        $posts = $this->postRepository->getLatestPostsFromUsers(
            $user,
            $followedAndSubscribedUsers,
            $perPage
        );

        // Apply geoblocking to filter out posts from blocked creators
        $locationService = app(\App\Services\LocationService::class);
        
        // Get user location data - use stored location or detect from IP
        $userLocation = null;
        if ($user->country_code) {
            // User has stored location data
            $userLocation = [
                'country_code' => $user->country_code,
                'country_name' => $user->country_name,
                'region_name' => $user->region_name,
                'city_name' => $user->city_name,
            ];
        } else {
            // Try to detect location from IP
            $userLocation = $locationService->getUserLocation(request()->ip());
        }
        
        // Get the IDs of creators who have blocked the viewer's location
        $blockedCreatorIds = collect();
        
        if ($userLocation && isset($userLocation['country_code'])) {
            $blockedCreatorIds = User::whereIn('id', $followedAndSubscribedUsers)
                ->whereHas('blockedLocations', function ($query) use ($userLocation) {
                    $query->where(function ($q) use ($userLocation) {
                        // Check country level
                        $q->where(function ($countryQ) use ($userLocation) {
                            $countryQ->where('location_type', 'country')
                                     ->where('country_code', $userLocation['country_code']);
                        });
                        
                        // Check region level with flexible matching
                        if (isset($userLocation['region_name']) && $userLocation['region_name']) {
                            $q->orWhere(function ($regionQ) use ($userLocation) {
                                $regionQ->where('location_type', 'region')
                                        ->where('country_code', $userLocation['country_code'])
                                        ->where(function ($flexibleQ) use ($userLocation) {
                                            // Exact match
                                            $flexibleQ->where('region_name', $userLocation['region_name'])
                                            // Partial matches (e.g., "Nairobi" in "Nairobi County")
                                            ->orWhere(function ($partialQ) use ($userLocation) {
                                                $partialQ->whereRaw('LOWER(region_name) LIKE ?', ['%' . strtolower($userLocation['region_name']) . '%'])
                                                        ->orWhereRaw('LOWER(?) LIKE CONCAT("%", LOWER(region_name), "%")', [$userLocation['region_name']]);
                                            });
                                        });
                            });
                        }
                        
                        // Check city level with flexible region matching
                        if (isset($userLocation['city_name']) && $userLocation['city_name']) {
                            $q->orWhere(function ($cityQ) use ($userLocation) {
                                $cityQ->where('location_type', 'city')
                                      ->where('country_code', $userLocation['country_code'])
                                      ->where('city_name', $userLocation['city_name'])
                                      ->where(function ($flexibleQ) use ($userLocation) {
                                          // Exact region match
                                          $flexibleQ->where('region_name', $userLocation['region_name'])
                                          // Partial region matches
                                          ->orWhere(function ($partialQ) use ($userLocation) {
                                              $partialQ->whereRaw('LOWER(region_name) LIKE ?', ['%' . strtolower($userLocation['region_name']) . '%'])
                                                      ->orWhereRaw('LOWER(?) LIKE CONCAT("%", LOWER(region_name), "%")', [$userLocation['region_name']]);
                                          });
                                      });
                            });
                        }
                    });
                })
                ->pluck('id');
        }

        // Filter out posts from blocked creators
        $posts->setCollection(
            $posts->getCollection()->filter(function ($post) use ($blockedCreatorIds) {
                return !$blockedCreatorIds->contains($post->user_id);
            })
        );

        // Filter out posts from creators who have disabled preview discovery
        $posts->setCollection(
            $posts->getCollection()->filter(function ($post) {
                // Check if the creator has disabled preview discovery
                $hasDisabledPreviewDiscovery = $post->user->settings()
                    ->whereHas('category', function ($query) {
                        $query->where('name', 'privacyAndSecurity');
                    })
                    ->where('key', 'enable_preview_discovery')
                    ->where('value', 'false')
                    ->exists();
                
                // If the setting is not set or is true, allow preview discovery
                return !$hasDisabledPreviewDiscovery;
            })
        );

        // Only keep posts with at least one video preview
        $posts->setCollection(
            $posts->getCollection()->filter(function ($post) {
                foreach ($post->media as $media) {
                    foreach ($media->previews as $preview) {
                        $url = is_array($preview) ? ($preview['url'] ?? '') : $preview->url;
                        if (preg_match('/\\.(mp4|webm|ogg)$/i', $url) || strpos($url, 'video') !== false) {
                            return true;
                        }
                    }
                }
                return false;
            })
        );

        // Filter out posts that the user has already purchased/accessed
        $posts->setCollection(
            $posts->getCollection()->filter(function ($post) use ($user) {
                // Check if user has permission to this post (meaning they've purchased it)
                $hasPermission = $this->permissionService->checkPermission($post, $user);
                
                // Only show previews for posts the user hasn't purchased yet
                return !$hasPermission;
            })
        );

        // Transform posts to include permission info
        $posts->getCollection()->transform(function ($post) use ($user) {
            $post->user_has_permission = $this->permissionService->checkPermission($post, $user);
            $post->required_permissions = $this->permissionService->getRequiredPermissions($post, $user);
            return $post;
        });

        $suggestedUsers = $this->getSuggestedUsers($user, 5);

        return [
            'posts' => $posts,
            'suggested_users' => $suggestedUsers,
        ];
    }

    /**
     * Get posts newer than the specified post ID
     *
     * @param User $user The user to get feed for
     * @param int $lastPostId The ID of the last post the user has seen
     * @return Collection
     */
    public function getNewPostsSince(User $user, int $lastPostId): Collection
    {
        // Get the users the current user follows
        $followedUsers = $user->following()->get();

        // Get posts newer than the last post ID
        $posts = $this->postRepository->getPostsNewerThan(
            $user,
            $followedUsers, // This is already an Eloquent Collection
            $lastPostId
        );

        // Add permission information to each post
        $posts->transform(function ($post) use ($user) {
            $post->user_has_permission = $this->permissionService->checkPermission($post, $user);
            $post->required_permissions = $this->permissionService->getRequiredPermissions($post, $user);
            return $post;
        });

        return $posts;
    }

    protected function getSuggestedUsers(User $user, int $limit = 5): Collection
    {
        // Get blocked and muted user IDs
        $blockedAndMutedUserIds = DB::table('list_members')
            ->join('list_types', 'list_members.list_type_id', '=', 'list_types.id')
            ->where('list_members.user_id', $user->id)
            ->whereIn('list_types.name', ['Blocked Accounts', 'Muted Accounts'])
            ->pluck('list_members.member_id');

        $suggestedUsers = $this->userRepository->getUsersNotFollowedBy($user, $limit, $blockedAndMutedUserIds);

        // Filter suggested users based on location blocking
        $suggestedUsers = $this->contentVisibilityService->filterUsersByBlockedLocations($user, $suggestedUsers);

        // Log removed for production

        return $suggestedUsers;
    }

    /**
     * Get feed posts for subscribed content only
     *
     * @param User $user The user to get feed for
     * @param int $perPage Number of posts per page
     * @return array
     */
    public function getSubscribedFeedPosts(User $user, int $perPage = 15): array
    {
        // Get active subscriptions and canceled subscriptions that haven't expired yet
        $now = Carbon::now();
        $subscribedUsers = $user->subscriptions()
            ->where(function($query) use ($now) {
                $query->where('status', Subscription::ACTIVE_STATUS)
                      ->orWhere(function($q) use ($now) {
                          $q->where('status', Subscription::CANCELED_STATUS)
                            ->where('start_date', '<=', $now)
                            ->where('end_date', '>=', $now);
                      });
            })
            ->pluck('creator_id');

        // Log removed for production

        // Get posts from subscribed users, sorted by ID in descending order (newest first)
        $posts = $this->postRepository->getLatestPostsFromUsers(
            $user,
            $subscribedUsers,
            $perPage
        );

        $posts->getCollection()->transform(function ($post) use ($user) {
            $post->user_has_permission = $this->permissionService->checkPermission($post, $user);
            $post->required_permissions = $this->permissionService->getRequiredPermissions($post, $user);
            return $post;
        });

        $suggestedUsers = $this->getSuggestedUsers($user, 5);

        // Log removed for production

        return [
            'posts' => $posts,
            'suggested_users' => $suggestedUsers,
        ];
    }

    /**
     * Get feed posts from a specific list
     *
     * @param User $user The user to get feed for
     * @param int $listId The ID of the list to filter by
     * @param int $perPage Number of posts per page
     * @return array
     */
    public function getListFeedPosts(User $user, int $listId, int $perPage = 15): array
    {
        // Get list members
        $listMembers = $this->listService->getListMembers($user, $listId);
        
        if (!$listMembers) {
            Log::warning('List not found or user does not have access', [
                'user_id' => $user->id,
                'list_id' => $listId
            ]);
            
            return [
                'posts' => collect([]),
                'suggested_users' => collect([]),
            ];
        }

        // Get user IDs from list members
        $listUserIds = $listMembers->pluck('id');

        // Check if this is the "Blocked Accounts" list (ID 4) or "Muted Accounts" list (ID 5)
        $isBlockedList = ($listId == 4); // "Blocked Accounts" list type ID
        $isMutedList = ($listId == 5);   // "Muted Accounts" list type ID
        $shouldBypassFiltering = $isBlockedList || $isMutedList;
        
        if ($shouldBypassFiltering) {
            // For "Blocked Accounts" or "Muted Accounts" lists, show posts FROM those users (don't filter them out)
            $posts = $this->postRepository->getAllPostsFromUsers(
                $listUserIds,
                $perPage
            );
        } else {
            // For normal lists, filter out blocked users
            $posts = $this->postRepository->getLatestPostsFromUsers(
                $user,
                $listUserIds,
                $perPage
            );
        }

        $posts->getCollection()->transform(function ($post) use ($user) {
            $post->user_has_permission = $this->permissionService->checkPermission($post, $user);
            $post->required_permissions = $this->permissionService->getRequiredPermissions($post, $user);
            return $post;
        });

        $suggestedUsers = $this->getSuggestedUsers($user, 5);

        // Log removed for production

        return [
            'posts' => $posts,
            'suggested_users' => $suggestedUsers,
        ];
    }

    public function getImagePreviewPosts(int $limit = 10)
    {
        $posts = $this->postRepository->getImagePreviewPosts($limit);

        $posts->transform(function ($post) {
            // No user permission logic needed for previews
            return $post;
        });

        return $posts;
    }

    public function getAllImagePreviews(int $limit = 100)
    {
        return $this->postRepository->getAllImagePreviews($limit);
    }

    /**
     * Get public previews for unauthenticated users (auth page)
     * Only returns posts with video previews, matching home page logic
     *
     * @param int $limit
     * @return Collection
     */
    public function getPublicPreviews(int $limit = 10): Collection
    {
        // Get random posts with media that are public
        $posts = $this->postRepository->getPublicPreviewPosts($limit);

        // Only keep posts with at least one video preview (matching home page logic)
        $posts = $posts->filter(function ($post) {
            foreach ($post->media as $media) {
                foreach ($media->previews as $preview) {
                    $url = is_array($preview) ? ($preview['url'] ?? '') : $preview->url;
                    if (preg_match('/\\.(mp4|webm|ogg)$/i', $url) || strpos($url, 'video') !== false) {
                        return true;
                    }
                }
            }
            return false;
        });

        // Transform posts to include basic info for public display
        $posts->transform(function ($post) {
            $post->user_has_permission = false; // Public users don't have permission
            $post->required_permissions = []; // No permissions required for public view
            $post->is_preview_video = true; // Mark as preview for auth page
            return $post;
        });

        Log::info('Public video previews fetched', [
            'posts_count' => $posts->count(),
        ]);

        return $posts;
    }

    /**
     * Search posts by query
     *
     * @param string $query Search query
     * @param User|null $user The user performing the search
     * @param int $perPage Number of posts per page
     * @return LengthAwarePaginator
     */
    public function searchPosts(string $query, ?User $user = null, int $perPage = 15): LengthAwarePaginator
    {
        $posts = $this->postRepository->searchPosts($query, $perPage);

        // Apply permission checks if user is provided
        if ($user) {
            $posts->getCollection()->transform(function ($post) use ($user) {
                $post->user_has_permission = $this->permissionService->checkPermission($post, $user);
                $post->required_permissions = $this->permissionService->getRequiredPermissions($post, $user);
                return $post;
            });
        }

        return $posts;
    }
}
