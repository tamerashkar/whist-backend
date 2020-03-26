<?php

namespace App\Listeners;

use App\Events\BiddingHasStarted;

class StartBidding
{
    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        $event->game->startBidding();

        event(new BiddingHasStarted($event->game));
    }
}
