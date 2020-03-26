<?php

namespace App\Listeners;

use App\Events\RoundHasEnded;
use App\Events\BidWinnerWasSelected;

class SelectBidWinner
{
    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        if ($event->game->hasBidWinner()) {
            $event->game->selectBidWinner();

            event(new BidWinnerWasSelected($event->game));
        } else {
            event(new RoundHasEnded($event->game));
        }
    }
}
