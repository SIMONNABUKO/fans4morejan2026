<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CreatorApplicationRejectedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $feedback;

    public function __construct($feedback = null)
    {
        $this->feedback = $feedback;
    }

    public function build()
    {
        return $this->subject('Your Creator Application Status Update')
                    ->markdown('emails.creator-application.rejected', [
                        'feedback' => $this->feedback
                    ]);
    }
} 