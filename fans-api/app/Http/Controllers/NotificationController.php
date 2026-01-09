<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    /**
     * Get all notifications for the authenticated user
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $notifications = $user->notifications()
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($notification) {
                $data = $notification->data;
                
                // Fix: Decode JSON data if it's stored as a string
                if (is_string($data)) {
                    $decodedData = json_decode($data, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $data = $decodedData;
                    } else {
                        Log::error('Failed to decode notification data JSON', [
                            'notification_id' => $notification->id,
                            'json_error' => json_last_error_msg(),
                            'raw_data' => $data
                        ]);
                        $data = [];
                    }
                }
                
                // If this is a tag request notification, fetch the latest tag status from post_tags table
                if (isset($data['type']) && $data['type'] === 'tag_request') {
                    // Try to get tag_id from either the tag object or direct tag_id field
                    $tagId = isset($data['tag']['id']) ? $data['tag']['id'] : ($data['tag_id'] ?? null);
                    
                    if ($tagId) {
                        $tag = \App\Models\PostTag::find($tagId);
                        if ($tag) {
                            // Always update with the latest status from database
                            $data['tag'] = [
                                'id' => $tag->id,
                                'status' => $tag->status,
                                'updated_at' => $tag->updated_at
                            ];
                            // Also update the legacy tag_status field for backwards compatibility
                            $data['tag_status'] = $tag->status;
                        }
                    }
                }
                
                return [
                    'id' => $notification->id,
                    'type' => $notification->type,
                    'data' => $data,
                    'read' => !is_null($notification->read_at),
                    'created_at' => $notification->created_at,
                ];
            });
            
        return response()->json($notifications);
    }
    
    /**
     * Mark a notification as read
     */
    public function markAsRead(Request $request, $id)
    {
        $user = Auth::user();
        $notification = $user->notifications()->where('id', $id)->first();
            
        if (!$notification) {
            return response()->json(['message' => 'Notification not found'], 404);
        }
        
        $notification->markAsRead();
        
        return response()->json(['message' => 'Notification marked as read']);
    }
    
    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(Request $request)
    {
        $user = Auth::user();
        $user->unreadNotifications->markAsRead();
            
        return response()->json(['message' => 'All notifications marked as read']);
    }
    
    /**
     * Get unread notification count
     */
    public function unreadCount(Request $request)
    {
        $user = Auth::user();
        $count = $user->unreadNotifications()->count();
        
        return response()->json(['count' => $count]);
    }
}