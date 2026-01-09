<?php

namespace App\Contracts;

use Illuminate\Database\Eloquent\Collection;
use App\Models\User;
use App\Models\Message;

interface MessageRepositoryInterface
{
    public function getRecentConversations(User $user, int $limit): Collection;
    public function getOrCreateConversation(User $user1, User $user2): array;
    public function getConversation(User $user1, User $user2): Collection;
    public function create(array $data): Message;
    public function markAsRead(Message $message): void;
    public function delete(Message $message): bool;
    public function getUnreadMessagesCount(User $user): int;
}

