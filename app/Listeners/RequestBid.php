<?php

namespace App\Listeners;

use App\Events\BiddingHasEnded;
use App\Events\BidWasRequested;

class RequestBid
{
    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        if ($event->game->hasReceivedAllBids()) {
            event(new BiddingHasEnded($event->game));
        } else {
            event(new BidWasRequested($event->game));
        }
    }
}
