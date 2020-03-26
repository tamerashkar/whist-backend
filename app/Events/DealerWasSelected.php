<?php

namespace App\Events;

class DealerWasSelected extends GameEvent implements Announceable
{
    public function message(): array
    {
        return [
            'player_id' => $this->game->dealer()->id,
            'body' => "Selected as the dealer"
        ];
    }
}
