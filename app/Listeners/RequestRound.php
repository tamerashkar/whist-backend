<?php

namespace App\Listeners;

use App\Events\GameHasEnded;
use App\Events\RoundWasRequested;

class RequestRound
{
    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        if ($event->game->hasWinner()) {
            event(new GameHasEnded($event->game));
        } else {
            event(new RoundWasRequested($event->game));
        }
    }
}
