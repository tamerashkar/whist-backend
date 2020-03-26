<?php

namespace App\Listeners;

use App\Events\DealerWasSelected;
use App\Exceptions\RoundHasNotStarted;

class SelectDealer
{
    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        if (!$count = $event->game->players()->count()) {
            throw new RoundHasNotStarted();
        }

        $position = $event->game->dealer_position % $count + 1;

        $event->game->setDealer($event->game->players()->where('position', $position)->first());

        event(new DealerWasSelected($event->game));
    }
}
