<?php

namespace App\Listeners;

class NextTurnToPlayCard
{
    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        // @todo(tamer) - It might be better to set turn to null
        // to make it clear it is no ones turn after all cards are received
        // When we have all our cards, our round as ended
        // and we should not move to the next turn.
        if (!$event->game->hasReceivedAllCards()) {
            $event->game->nextTurnFrom(
                $event->game->player($event->player)->first()->pivot->position
            );
        }
    }
}
