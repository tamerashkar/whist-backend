<?php

namespace App\Listeners;

use App\Events\PlayerHasBid;

class NextTurnToBid
{
    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(PlayerHasBid $event)
    {
        // When we have all our bids, our bidding round as ended
        // and we should not move to the next turn.
        if (!$event->game->hasReceivedAllBids()) {
            $event->game->nextTurnFrom(
                $event->game->player($event->player)->first()->pivot->position
            );
        }
    }
}
