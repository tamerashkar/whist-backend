<?php

namespace App\Listeners;

use App\Events\HandHasEnded;
use App\Events\CardWasRequested;

class RequestCard
{
    /*
    * Handle the event.
    *
    * @param  object  $event
    * @return void
    */
    public function handle($event)
    {
        if ($event->game->hasReceivedAllCards()) {
            event(new HandHasEnded($event->game));
        } else {
            event(new CardWasRequested($event->game));
        }
    }
}
