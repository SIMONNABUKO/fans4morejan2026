<?php

namespace App\Notifications;

use App\Mail\CreatorApplicationRejectedMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class CreatorApplicationRejectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $applicationId;
    protected $feedback;

    public function __construct($applicationId, $feedback = null)
    {
        $this->applicationId = $applicationId;
        $this->feedback = $feedback;
    }

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast', 'mail'];
    }

    public function toMail(object $notifiable)
    {
        return new CreatorApplicationRejectedMail($this->feedback);
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'application_id' => $this->applicationId,
            'feedback' => $this->feedback,
            'type' => 'creator_application_rejected',
            'message' => 'Your creator application has been rejected.'
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toDatabase($notifiable));
    }
} 