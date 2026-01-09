<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreatePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Http\Requests\CommentRequest;
use App\Models\Post;
use App\Services\PostService;
use App\Http\Resources\PostResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PostController extends Controller
{
    protected $postService;

    public function __construct(PostService $postService)
    {
        $this->postService = $postService;
    }

    public function store(CreatePostRequest $request): JsonResponse
    {
        $post = $this->postService->createPost($request->validated(), $request->user());
        return response()->json(new PostResource($post->load('media.previews')), 201);
    }

    public function index(Request $request): JsonResponse
    {
        $viewAsFollower = $request->query('view_as_follower', false);
        $posts = $this->postService->getUserPostsWithPinnedFirst($request->user(), 15, $viewAsFollower);
        return response()->json(PostResource::collection($posts));
    }

    /**
     * Display the specified post.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            // Get the post with its relationships
            $post = $this->postService->getPost($id);
            
            if (!$post) {
                return response()->json(['error' => 'Post not found'], 404);
            }
            // Log the post data for debugging
            Log::info('PostController@show post data', [
                'post_id' => $id,
                'post' => $post->toArray()
            ]);
            return response()->json(new PostResource($post));
        } catch (\Exception $e) {
            Log::error('Error fetching post:', [
                'post_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Failed to fetch post'], 500);
        }
    }

    public function update(UpdatePostRequest $request, $post): JsonResponse
    {
        $postModel = Post::find($post);
        Log::info('PostController@update manual find', [
            'route_param_post' => $post,
            'found_post' => $postModel,
            'found_post_id' => $postModel ? $postModel->id : null,
        ]);
        if (!$postModel) {
            return response()->json(['error' => 'Post not found'], 404);
        }
        $updatedPost = $this->postService->updatePost($postModel, $request->validated());
        return response()->json(new PostResource($updatedPost->load('media.previews')));
    }

    public function destroy(Post $post): JsonResponse
    {
        $this->postService->deletePost($post);
        return response()->json(null, 204);
    }

    /**
     * Pin a post
     */
    public function pinPost(int $post): JsonResponse
    {
        // Find the post manually
        $postModel = Post::find($post);
        
        // Debug logging for live environment
        Log::info('PostController@pinPost called', [
            'post_id' => $postModel->id ?? 'NULL',
            'post_user_id' => $postModel->user_id ?? 'NULL',
            'auth_user_id' => Auth::id(),
            'auth_user' => Auth::user() ? Auth::user()->id : null,
            'route_post_param' => $post,
            'post_exists' => $postModel ? 'YES' : 'NO',
            'post_class' => $postModel ? get_class($postModel) : 'NULL',
            'post_attributes' => $postModel ? $postModel->getAttributes() : [],
            'post_raw' => $postModel ? $postModel->toArray() : [],
        ]);
        
        // Check if post exists
        if (!$postModel) {
            Log::error('PostController@pinPost - Post not found', [
                'route_post_param' => $post,
                'auth_user_id' => Auth::id(),
            ]);
            return response()->json(['error' => 'Post not found'], 404);
        }
        
        // Check if the user owns the post
        if ($postModel->user_id !== Auth::id()) {
            Log::warning('PostController@pinPost unauthorized', [
                'post_user_id' => $postModel->user_id,
                'auth_user_id' => Auth::id(),
                'mismatch' => $postModel->user_id !== Auth::id()
            ]);
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $this->postService->pinPost($postModel);
        return response()->json(['message' => 'Post pinned successfully']);
    }

    /**
     * Unpin a post
     */
    public function unpinPost(int $post): JsonResponse
    {
        // Find the post manually
        $postModel = Post::find($post);
        
        // Check if post exists
        if (!$postModel) {
            Log::error('PostController@unpinPost - Post not found', [
                'route_post_param' => $post,
                'auth_user_id' => Auth::id(),
            ]);
            return response()->json(['error' => 'Post not found'], 404);
        }
        
        // Check if the user owns the post
        if ($postModel->user_id !== Auth::id()) {
            Log::warning('PostController@unpinPost unauthorized', [
                'post_user_id' => $postModel->user_id,
                'auth_user_id' => Auth::id(),
                'mismatch' => $postModel->user_id !== Auth::id()
            ]);
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $this->postService->unpinPost($postModel);
        return response()->json(['message' => 'Post unpinned successfully']);
    }

    public function like(Post $post): JsonResponse
    {
        $this->postService->likePost($post, auth()->user());
        return response()->json(['message' => 'Post liked successfully']);
    }

    public function unlike(Post $post): JsonResponse
    {
        $this->postService->unlikePost($post, auth()->user());
        return response()->json(['message' => 'Post unliked successfully']);
    }

    public function getComments(Post $post): JsonResponse
    {
        $comments = $this->postService->getPostComments($post);
        return response()->json($comments);
    }

    public function addComment(CommentRequest $request, Post $post): JsonResponse
    {
        $comment = $this->postService->addComment($post, auth()->user(), $request->validated()['content']);
        return response()->json($comment, 201);
    }

    /**
     * Get the current user's scheduled (pending) posts
     * @return \Illuminate\Http\JsonResponse
     */
    public function getScheduledPosts(Request $request)
    {
        $user = $request->user();
        $posts = \App\Models\Post::where('user_id', $user->id)
            ->where('status', 'pending')
            ->with(['media.previews', 'user', 'permissionSets.permissions', 'stats', 'tags'])
            ->orderBy('scheduled_for', 'asc')
            ->get();

        return \App\Http\Resources\PostResource::collection($posts);
    }
}

