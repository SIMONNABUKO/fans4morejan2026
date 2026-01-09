<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CreatorApplicationApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $feedback;

    public function __construct($feedback = null)
    {
        $this->feedback = $feedback;
    }

    public function build()
    {
        return $this->subject('Your Creator Application has been Approved!')
                    ->markdown('emails.creator-application.approved', [
                        'feedback' => $this->feedback
                    ]);
    }
} 