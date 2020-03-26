<?php

namespace App\Listeners;

use App\Events\HandHasStarted;

class StartHand
{
    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        $event->game->compete();

        event(new HandHasStarted($event->game));
    }
}
