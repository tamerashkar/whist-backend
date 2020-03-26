<?php

namespace App\Listeners;

use App\Events\PlayerHasPlayedCard;

class Play
{
    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        $player = $event->game->activePlayer();

        if ($player->isRobot()) {
            $player->robot()->play($event->game);

            event(new PlayerHasPlayedCard($event->game, $player->fresh()));
        }
    }
}
