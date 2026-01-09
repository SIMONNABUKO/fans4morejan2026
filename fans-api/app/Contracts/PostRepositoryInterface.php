<?php

namespace App\Contracts;

use App\Models\Post;
use App\Models\User;
use App\Models\Comment;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface PostRepositoryInterface
{
    public function create(array $data, User $user): Post;
    public function update(Post $post, array $data): Post;
    public function delete(Post $post): bool;
    public function findById(int $id): ?Post;
    public function getPostsForUser(User $user, int $perPage = 15): LengthAwarePaginator;
    public function getVisiblePosts(User $viewer, int $perPage = 15): LengthAwarePaginator;
    public function addLike(Post $post, User $user): void;
    public function removeLike(Post $post, User $user): void;
    public function getComments(Post $post): LengthAwarePaginator;
    public function addComment(Post $post, User $user, string $content): Comment;
    
    /**
     * Get latest posts from specified users
     *
     * @param User $user The user requesting the posts
     * @param Collection $userIds IDs of users whose posts can be shown
     * @param int $perPage Number of posts per page
     * @return LengthAwarePaginator
     */
    public function getLatestPostsFromUsers(User $user, Collection $userIds, int $perPage = 15): LengthAwarePaginator;
    
    /**
     * Get posts newer than the specified post ID from followed users
     *
     * @param User $user The user requesting the posts
     * @param Collection $followedUserIds IDs of users the current user follows
     * @param int $lastPostId The ID of the last post the user has seen
     * @return Collection
     */
    public function getPostsNewerThan(User $user, Collection $followedUserIds, int $lastPostId): Collection;
    
    /**
     * Pin a post
     */
    public function pinPost(Post $post): void;
    
    /**
     * Unpin a post
     */
    public function unpinPost(Post $post): void;
    
    /**
     * Get posts for user with pinned posts first
     */
    public function getPostsForUserWithPinnedFirst(User $user, int $perPage = 15, bool $viewAsFollower = false): LengthAwarePaginator;
}