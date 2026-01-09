<?php

namespace App\Http\Controllers;

use App\Http\Requests\CommentRequest;
use App\Models\Post;
use App\Models\Comment;
use App\Services\CommentService;
use App\Services\StatsService;
use App\Services\MediaStorageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    protected $commentService;
    protected $statsService;

    public function __construct(CommentService $commentService, StatsService $statsService)
    {
        $this->commentService = $commentService;
        $this->statsService = $statsService;
    }

    public function index(Post $post): JsonResponse
    {
        $comments = $this->commentService->getPostComments($post);
        return response()->json($comments);
    }

    public function store(CommentRequest $request, $post): JsonResponse
    {
        $post = \App\Models\Post::find($post);
        if (!$post) {
            return response()->json(['error' => 'Post not found'], 404);
        }
        Log::info('Post ID in CommentController@store', ['post_id' => $post->id]);
        $data = $request->validated();
        unset($data['post_id']);
        if ($request->hasFile('media')) {
            $path = app(MediaStorageService::class)
                ->storeUploadedFile('comments/media', $request->file('media'));
            $data['media_url'] = $path;
        }
        unset($data['media']);
        $comment = $this->commentService->addComment($post, Auth::user(), $data);
        $this->statsService->incrementComments($post);
        $post->load('stats');
        return response()->json(new \App\Http\Resources\PostResource($post), 201);
    }

    public function update(CommentRequest $request, Comment $comment): JsonResponse
    {
        $this->authorize('update', $comment);
        $data = $request->validated();
        unset($data['post_id']);
        if ($request->hasFile('media')) {
            $path = app(MediaStorageService::class)
                ->storeUploadedFile('comments/media', $request->file('media'));
            $data['media_url'] = $path;
        }
        unset($data['media']);
        $updatedComment = $this->commentService->updateComment($comment, $data);
        return response()->json($updatedComment);
    }

    public function destroy(Comment $comment): JsonResponse
    {
        $this->authorize('delete', $comment);
        $this->commentService->deleteComment($comment);
        return response()->json(null, 204);
    }
}
