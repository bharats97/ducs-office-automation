<?php

namespace App\Listeners;

use App\Events\ScholarCreated;
use App\Mail\UserRegisteredMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendRegisteredEmail
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     *
     * @return void
     */
    public function handle(ScholarCreated $event)
    {
        Mail::to($event->scholar)
        ->send(new UserRegisteredMail($event->scholar, $event->plainPassword));
    }
}
