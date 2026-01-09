<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReferralEarningNotification extends Notification implements ShouldQueue
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
            ->subject('New Referral Earnings!')
            ->line('You have earned a new referral commission!')
            ->line('Amount: $' . number_format($this->data['amount'], 2))
            ->line('From: ' . $this->data['source'])
            ->action('View Your Earnings', url('/referrals/earnings'))
            ->line('Thank you for being part of our community!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'referral_earning',
            'amount' => $this->data['amount'],
            'source' => $this->data['source'],
            'message' => 'You have earned $' . number_format($this->data['amount'], 2) . ' from ' . $this->data['source']
        ];
    }
} 