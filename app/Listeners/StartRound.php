<?php

namespace App\Listeners;

use App\Events\RoundHasStarted;

class StartRound
{
    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        $event->game->startRound();

        event(new RoundHasStarted($event->game));
    }
}
