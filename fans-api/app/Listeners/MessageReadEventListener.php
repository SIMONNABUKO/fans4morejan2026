<?php

namespace App\Listeners;

use App\Events\MessageReadEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class MessageReadEventListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\MessageReadEvent  $event
     * @return void
     */
    public function handle(MessageReadEvent $event)
    {
        Log::info('👁️ MessageReadEventListener handling event', [
            'message_id' => $event->message->id,
            'read_at' => $event->message->read_at
        ]);
        
        // You can add additional processing here if needed
        // For example, updating analytics, etc.
    }
}

