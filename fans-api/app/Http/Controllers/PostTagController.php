<?php

namespace App\Http\Controllers;

use App\Http\Requests\TagResponseRequest;
use App\Models\Post;
use App\Models\PostTag;
use App\Models\User;
use App\Services\PostTagService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PostTagController extends Controller
{
    protected $postTagService;

    public function __construct(PostTagService $postTagService)
    {
        $this->postTagService = $postTagService;
    }

    /**
     * Get pending tag requests for authenticated user
     *
     * @return JsonResponse
     */
    public function getPendingRequests(): JsonResponse
    {
        $pendingTags = PostTag::where('user_id', auth()->id())
            ->where('status', PostTag::STATUS_PENDING)
            ->with(['post', 'post.user', 'post.media'])
            ->get();

        Log::info('Retrieved pending tag requests', [
            'user_id' => auth()->id(),
            'count' => $pendingTags->count()
        ]);

        return response()->json($pendingTags);
    }

    /**
     * Respond to a tag request (approve or reject)
     *
     * @param TagResponseRequest $request
     * @return JsonResponse
     */
    public function respondToTagRequest(TagResponseRequest $request): JsonResponse
    {
        try {
            $tag = PostTag::findOrFail($request->tag_id);

            Log::info('Tag response request received', [
                'tag_id' => $tag->id,
                'user_id' => auth()->id(),
                'post_id' => $tag->post_id,
                'response' => $request->response
            ]);

            // Check if user is authorized to respond to this tag
            if ($tag->user_id !== auth()->id()) {
                Log::warning('Unauthorized tag response attempt', [
                    'tag_id' => $tag->id,
                    'tag_user_id' => $tag->user_id,
                    'auth_user_id' => auth()->id()
                ]);

                return response()->json([
                    'message' => 'Unauthorized action'
                ], 403);
            }

            // Check if tag is already processed
            if ($tag->status !== PostTag::STATUS_PENDING) {
                Log::warning('Attempted to respond to a non-pending tag', [
                    'tag_id' => $tag->id,
                    'current_status' => $tag->status
                ]);

                return response()->json([
                    'message' => 'This tag request has already been processed',
                    'status' => $tag->status
                ], 422);
            }

            $success = false;
            if ($request->response === 'approve') {
                $success = $this->postTagService->approveTag($tag);
                $message = 'Tag request approved';
                $status = 'approved';
            } else {
                $success = $this->postTagService->rejectTag($tag);
                $message = 'Tag request rejected';
                $status = 'rejected';
            }

            if (!$success) {
                return response()->json([
                    'message' => 'Failed to process tag request'
                ], 500);
            }

            // Get the updated post status
            $post = Post::findOrFail($tag->post_id);

            return response()->json([
                'message' => $message,
                'tag' => [
                    'id' => $tag->id,
                    'status' => $status,
                    'updated_at' => $tag->updated_at
                ],
                'post' => [
                    'id' => $post->id,
                    'status' => $post->status
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error responding to tag request', [
                'error' => $e->getMessage(),
                'tag_id' => $request->tag_id ?? null
            ]);

            return response()->json([
                'message' => 'An error occurred while processing your request'
            ], 500);
        }
    }

    /**
     * Get all tags for a post
     *
     * @param Post $post
     * @return JsonResponse
     */
    public function getPostTags(Post $post): JsonResponse
    {
        $tags = PostTag::where('post_id', $post->id)
            ->with('user')
            ->get();

        Log::info('Retrieved post tags', [
            'post_id' => $post->id,
            'count' => $tags->count()
        ]);

        return response()->json($tags);
    }

    /**
     * Search for users to tag
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function searchUsers(Request $request): JsonResponse
    {
        $request->validate([
            'query' => 'required|string|min:2'
        ]);

        $searchQuery = $request->input('query');

        $users = User::where('name', 'like', '%' . $searchQuery . '%')
            ->orWhere('username', 'like', '%' . $searchQuery . '%')
            ->select('id', 'name', 'username', 'avatar')
            ->limit(10)
            ->get();

        Log::info('User search for tagging', [
            'query' => $searchQuery,
            'results_count' => $users->count()
        ]);

        return response()->json($users);
    }

    /**
     * Get users previously tagged by the current user
     *
     * @return JsonResponse
     */
    public function getPreviouslyTaggedUsers(): JsonResponse
    {
        $users = $this->postTagService->getPreviouslyTaggedUsers(auth()->id());

        Log::info('Retrieved previously tagged users for current user', [
            'user_id' => auth()->id(),
            'count' => $users->count()
        ]);

        return response()->json($users);
    }
}
