<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewReferralNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private $data;

    /**
     * Create a new notification instance.
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Referral Signup!')
            ->line('Great news! Someone has signed up using your referral link.')
            ->line('User ' . $this->data['referred_name'] . ' has joined the platform.')
            ->line('You will earn 1% of their purchases as referral commission.')
            ->action('View Your Referrals', url('/referrals'))
            ->line('Thank you for helping our community grow!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'new_referral',
            'referred_id' => $this->data['referred_id'],
            'referred_name' => $this->data['referred_name'],
            'message' => 'New user ' . $this->data['referred_name'] . ' has signed up using your referral link.'
        ];
    }
} 