<?php

namespace App\Listeners;

use App\Events\CreditsWereRequested;

class RequestCredits
{
    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        event(new CreditsWereRequested($event->game));
    }
}
