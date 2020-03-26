<?php

namespace App\Listeners;

use App\Events\RoundHasEnded;
use App\Events\HandWasRequested;

class RequestHand
{
    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        if ($event->game->hasReceivedAllHands()) {
            event(new RoundHasEnded($event->game));
        } else {
            event(new HandWasRequested($event->game));
        }
    }
}
