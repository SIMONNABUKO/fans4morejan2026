<?php
namespace App\Notifications;

use App\Models\Post;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class PostTagApproved extends Notification implements ShouldQueue
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
        $url = url('/posts/' . $this->post->id);
        
        return (new MailMessage)
            ->subject($this->taggedUser->name . ' approved your tag request')
            ->line($this->taggedUser->name . ' has approved your tag request.')
            ->line('Your post has been published.')
            ->action('View Post', $url);
    }

    public function toDatabase($notifiable)
    {
        return [
            'post_id' => $this->post->id,
            'tagged_user_id' => $this->taggedUser->id,
            'tagged_user_name' => $this->taggedUser->name,
            'message' => $this->taggedUser->name . ' approved your tag request',
            'type' => 'tag_approved',
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
            'message' => $this->taggedUser->name . ' approved your tag request',
            'type' => 'tag_approved',
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