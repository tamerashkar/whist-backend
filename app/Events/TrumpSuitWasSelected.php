<?php

namespace App\Events;

class TrumpSuitWasSelected extends GameEvent implements Announceable
{
    public function message(): array
    {
        $suit = $this->game->suit();
        $player = $this->game->firstPlayerOfHand();

        return [
            'player_id' => $player->id,
            'body' => "Selected {$suit} as the trump card"
        ];
    }
}
