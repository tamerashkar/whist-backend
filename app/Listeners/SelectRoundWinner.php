<?php

namespace App\Listeners;

use App\Events\RoundWinnerWasSelected;

class SelectRoundWinner
{
    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        $event->game->selectRoundWinner();

        event(new RoundWinnerWasSelected($event->game));
    }
}
