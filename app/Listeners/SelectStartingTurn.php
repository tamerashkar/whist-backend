<?php

namespace App\Listeners;

use App\Events\HandWasRequested;
use App\Events\DealerWasSelected;
use App\Events\BidWinnerWasSelected;
use App\Events\HandWinnerWasSelected;

class SelectStartingTurn
{
    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        if ($event instanceof DealerWasSelected) {
            $event->game
                ->startTurnFrom($event->game->dealer()->pivot->position)
                ->nextTurnFrom($event->game->dealer()->pivot->position);
        } else if ($event instanceof BidWinnerWasSelected) {
            $event->game
                ->setStartTurn($event->game->bidWinner()->pivot->position)
                ->setNextTurn($event->game->bidWinner()->pivot->position);
        } else if ($event instanceof HandWinnerWasSelected) {
            $event->game
                ->setStartTurn($event->game->handWinner()->pivot->position)
                ->setNextTurn($event->game->handWinner()->pivot->position);
        } else if ($event instanceof HandWasRequested) {
            $winner = $event->game->handWinner() ?: $event->game->bidWinner();

            $event->game
                ->setStartTurn($winner->pivot->position)
                ->setNextTurn($winner->pivot->position);
        }
    }
}
