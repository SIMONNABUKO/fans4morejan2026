<?php

namespace App\Notifications;

use App\Mail\CreatorApplicationApprovedMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class CreatorApplicationApprovedNotification extends Notification implements ShouldQueue
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
        return new CreatorApplicationApprovedMail($this->feedback);
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'application_id' => $this->applicationId,
            'feedback' => $this->feedback,
            'type' => 'creator_application_approved',
            'message' => 'Your creator application has been approved!'
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toDatabase($notifiable));
    }
} 