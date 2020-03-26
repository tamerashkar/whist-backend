<?php

namespace App\Listeners;

use App\Events\PlayerHasBid;

class Bid
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
            $bid = $player->robot()->bidOn($event->game);

            event(new PlayerHasBid($event->game, $player->fresh(), $bid));
        }
    }
}
