<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewFollower extends Mailable
{
    use Queueable, SerializesModels;

    public $followerData;

    public function __construct($followerData)
    {
        $this->followerData = $followerData;
    }

    public function envelope()
    {
        return new Envelope(
            subject: 'You Have a New Follower',
        );
    }

    public function content()
    {
        return new Content(
            view: 'emails.new-follower',
        );
    }

    public function attachments()
    {
        return [];
    }
}

