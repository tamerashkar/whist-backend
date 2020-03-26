<?php

namespace App\Listeners;

use App\Events\TrumpSuitWasSelected;

class SelectTrumpSuit
{
    public function handle($event)
    {
        if (!$event->game->trumpSuit()) {
            $event->game->selectTrumpSuit();

            event(new TrumpSuitWasSelected($event->game));
        }
    }
}
