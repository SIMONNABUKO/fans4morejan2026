<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function index()
    {
        $comments = Comment::with('user', 'post')
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        return response()->json($comments);
    }

    public function show($id)
    {
        $comment = Comment::with('user', 'post')
            ->findOrFail($id);
        
        return response()->json($comment);
    }

    public function destroy($id)
    {
        $comment = Comment::findOrFail($id);
        $comment->delete();
        
        return response()->json(['message' => 'Comment deleted successfully']);
    }
} 