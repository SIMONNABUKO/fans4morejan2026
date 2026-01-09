<?php

namespace App\Services;

use App\Models\Post;
use App\Models\User;
use App\Models\Comment;
use Illuminate\Pagination\LengthAwarePaginator;

class CommentService
{
    public function getPostComments(Post $post): LengthAwarePaginator
    {
        return $post->comments()->with('user')->latest()->paginate(15);
    }

    public function addComment(Post $post, User $user, array $data): Comment
    {
        unset($data['post_id']); // Prevent overriding the relationship
        return $post->comments()->create(array_merge([
            'user_id' => $user->id,
        ], $data));
    }

    public function updateComment(Comment $comment, array $data): Comment
    {
        $comment->update($data);
        return $comment->fresh();
    }

    public function deleteComment(Comment $comment): void
    {
        $comment->delete();
    }
}

