<?php

namespace App\Repositories;

use App\Contracts\MessageRepositoryInterface;
use App\Models\Message;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MessageRepository implements MessageRepositoryInterface
{
    public function getOrCreateConversation(User $user1, User $user2): array
    {
        $conversation = $this->getConversation($user1, $user2);
        
        if ($conversation->isEmpty()) {
            // Create an empty message to start the conversation
            $this->create([
                'sender_id' => $user1->id,
                'receiver_id' => $user2->id,
                'content' => '',
                'is_empty' => true,
            ]);
            
            $conversation = $this->getConversation($user1, $user2);
        }
        
        // $user2 is always the "other" user in the conversation
        $otherUser = $user2;
        
        return [
            'user' => [
                'id' => $otherUser->id,
                'name' => $otherUser->name,
                'avatar' => $otherUser->avatar,
                'username' => $otherUser->username,
                'verified' => $otherUser->verified,
            ],
            'messages' => $conversation->map(function ($message) use ($user1, $user2) {
                return [
                    'id' => $message->id,
                    'sender_id' => $message->sender_id,
                    'receiver_id' => $message->receiver_id,
                    'content' => $message->content,
                    'created_at' => $message->created_at,
                    'updated_at' => $message->updated_at,
                    'read_at' => $message->read_at,
                    'is_from_user1' => $message->sender_id === $user1->id,
                    'media' => $message->media->map(function ($media) {
                        return [
                            'id' => $media->id,
                            'url' => $media->url,
                            'type' => $media->type,
                        ];
                    }),
                ];
            }),
        ];
    }

    public function getConversation(User $user1, User $user2): Collection
    {
        return Message::with('media')
            ->where(function ($query) use ($user1, $user2) {
                $query->where('sender_id', $user1->id)->where('receiver_id', $user2->id);
            })->orWhere(function ($query) use ($user1, $user2) {
                $query->where('sender_id', $user2->id)->where('receiver_id', $user1->id);
            })->orderBy('created_at', 'asc')->get();
    }

    public function create(array $data): Message
    {
        return Message::create($data);
    }

    public function markAsRead(Message $message): void
    {
        Log::info('📝 Marking message as read in repository', [
            'message_id' => $message->id,
            'previous_read_at' => $message->read_at
        ]);
        
        if (!$message->read_at) {
            $message->read_at = now();
            $result = $message->save();
            
            Log::info('✅ Message marked as read', [
                'message_id' => $message->id,
                'read_at' => $message->read_at,
                'result' => $result
            ]);
        } else {
            Log::info('⚠️ Message was already marked as read', [
                'message_id' => $message->id,
                'read_at' => $message->read_at
            ]);
        }
    }

    public function delete(Message $message): bool
    {
        return $message->delete();
    }

    public function getUnreadMessagesCount(User $user): int
    {
        return Message::where('receiver_id', $user->id)
            ->whereNull('read_at')
            ->count();
    }

    public function getRecentConversations(User $user, int $limit): Collection
    {
        try {
            return Message::with(['sender', 'receiver'])
                ->where(function ($query) use ($user) {
                    $query->where('sender_id', $user->id)
                          ->orWhere('receiver_id', $user->id);
                })
                ->select('id', 'sender_id', 'receiver_id', 'content', 'created_at', 'read_at')
                ->orderBy('created_at', 'desc')
                ->get()
                ->unique(function ($item) use ($user) {
                    return $item->sender_id == $user->id ? $item->receiver_id : $item->sender_id;
                })
                ->take($limit)
                ->map(function ($message) use ($user) {
                    $otherUser = $message->sender_id == $user->id ? $message->receiver : $message->sender;
                    $message->other_user_id = $otherUser->id;
                    $message->other_user = $otherUser;
                    return $message;
                })
                ->values();
        } catch (\Exception $e) {
            Log::error('Error in MessageRepository getRecentConversations: ' . $e->getMessage());
            throw $e;
        }
    }
}

