<?php
// app/Repositories/PostTagRepository.php
namespace App\Repositories;

use App\Models\Post;
use App\Models\PostTag;
use App\Models\User;

class PostTagRepository
{
    /**
     * Get all tags for a post
     *
     * @param int $postId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPostTags(int $postId)
    {
        return PostTag::where('post_id', $postId)
            ->with('user')
            ->get();
    }
    
    /**
     * Get pending tag requests for a user
     *
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPendingTagRequests(int $userId)
    {
        return PostTag::where('user_id', $userId)
            ->where('status', PostTag::STATUS_PENDING)
            ->with(['post', 'post.user'])
            ->get();
    }
    
    /**
     * Find a tag by post and user
     *
     * @param int $postId
     * @param int $userId
     * @return PostTag|null
     */
    public function findTag(int $postId, int $userId)
    {
        return PostTag::where('post_id', $postId)
            ->where('user_id', $userId)
            ->first();
    }
    
    /**
     * Create a new tag
     *
     * @param array $data
     * @return PostTag
     */
    public function createTag(array $data)
    {
        return PostTag::create($data);
    }
    
    /**
     * Update a tag's status
     *
     * @param int $tagId
     * @param string $status
     * @return bool
     */
    public function updateTagStatus(int $tagId, string $status)
    {
        $tag = PostTag::findOrFail($tagId);
        $tag->status = $status;
        return $tag->save();
    }
    
    /**
     * Delete a tag
     *
     * @param int $tagId
     * @return bool
     */
    public function deleteTag(int $tagId)
    {
        $tag = PostTag::findOrFail($tagId);
        return $tag->delete();
    }
}