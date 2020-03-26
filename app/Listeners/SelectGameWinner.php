<?php

namespace App\Listeners;

use App\Events\GameWinnerWasSelected;

class SelectGameWinner
{
    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        $event->game->selectGameWinner();

        event(new GameWinnerWasSelected($event->game));
    }
}
