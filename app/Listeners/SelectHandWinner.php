<?php

namespace App\Listeners;

use App\Events\HandWinnerWasSelected;

class SelectHandWinner
{
    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        $event->game->selectHandWinner();

        event(new HandWinnerWasSelected($event->game));
    }
}
