<?php
namespace App\Notifications;

use App\Models\Post;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class PostTagRejected extends Notification implements ShouldQueue
{
    use Queueable;

    protected $post;
    protected $taggedUser;

    public function __construct(Post $post, User $taggedUser)
    {
        $this->post = $post;
        $this->taggedUser = $taggedUser;
    }

    public function via($notifiable)
    {
        // Add 'broadcast' to the channels
        return ['mail', 'database', 'broadcast'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject($this->taggedUser->name . ' rejected your tag request')
            ->line($this->taggedUser->name . ' has rejected your tag request.')
            ->line('You can still publish your post without this tag.');
    }

    public function toDatabase($notifiable)
    {
        return [
            'post_id' => $this->post->id,
            'tagged_user_id' => $this->taggedUser->id,
            'tagged_user_name' => $this->taggedUser->name,
            'message' => $this->taggedUser->name . ' rejected your tag request',
            'type' => 'tag_rejected',
        ];
    }
    
    /**
     * Get the broadcastable representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return BroadcastMessage
     */
    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'post_id' => $this->post->id,
            'tagged_user_id' => $this->taggedUser->id,
            'tagged_user_name' => $this->taggedUser->name,
            'message' => $this->taggedUser->name . ' rejected your tag request',
            'type' => 'tag_rejected',
            'tagged_user' => [
                'id' => $this->taggedUser->id,
                'name' => $this->taggedUser->name,
                'username' => $this->taggedUser->username,
                'avatar' => $this->taggedUser->avatar,
                'verified' => $this->taggedUser->verified ?? false,
            ],
            'post' => [
                'id' => $this->post->id,
                'content' => substr($this->post->content ?? '', 0, 100) . '...',
            ],
            'time' => now()->toIso8601String(),
        ]);
    }
}