<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MediaLikeNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $likeData;

    public function __construct($likeData)
    {
        $this->likeData = $likeData;
    }

    public function envelope()
    {
        return new Envelope(
            subject: 'Your Media Received a Like',
        );
    }

    public function content()
    {
        return new Content(
            view: 'emails.media-like',
        );
    }

    public function attachments()
    {
        return [];
    }
}

