<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class NewLikeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $liker;
    protected $post;
    protected $reactionType;

    /**
     * Create a new notification instance.
     */
    public function __construct(User $liker, $post, string $reactionType = 'heart')
    {
        $this->liker = $liker;
        $this->post = $post;
        $this->reactionType = $reactionType;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->liker->name . ' liked your post')
            ->line($this->liker->name . ' liked your post.')
            ->action('View Post', url('/posts/' . $this->post->id));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'liker_id' => $this->liker->id,
            'liker_name' => $this->liker->name,
            'liker_username' => $this->liker->username,
            'post_id' => $this->post->id,
            'message' => $this->liker->name . ' liked your post',
            'type' => 'like',
            'reaction_type' => $this->reactionType,
        ];
    }
    
    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'liker_id' => $this->liker->id,
            'liker_name' => $this->liker->name,
            'liker_username' => $this->liker->username,
            'post_id' => $this->post->id,
            'message' => $this->liker->name . ' liked your post',
            'type' => 'like',
            'reaction_type' => $this->reactionType,
            'liker' => [
                'id' => $this->liker->id,
                'name' => $this->liker->name,
                'username' => $this->liker->username,
                'avatar' => $this->liker->avatar,
                'verified' => $this->liker->verified ?? false,
            ],
            'post' => [
                'id' => $this->post->id,
                'content' => substr($this->post->content ?? '', 0, 100) . '...',
            ],
            'time' => now()->toIso8601String(),
        ]);
    }
}