<?php

namespace App\Jobs;

use App\Models\Referral;
use App\Models\ReferralEarning;
use App\Notifications\NewReferralNotification;
use App\Notifications\ReferralEarningNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendReferralNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $notifiable;
    private $type;
    private $data;

    /**
     * Create a new job instance.
     */
    public function __construct($notifiable, string $type, array $data)
    {
        $this->notifiable = $notifiable;
        $this->type = $type;
        $this->data = $data;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        switch ($this->type) {
            case 'new_referral':
                $this->notifiable->notify(new NewReferralNotification($this->data));
                break;
            case 'referral_earning':
                $this->notifiable->notify(new ReferralEarningNotification($this->data));
                break;
        }
    }
} 