<?php

namespace App\Events;

class HandWinnerWasSelected extends GameEvent
{
    public function message(): array
    {
        $winner = $this->game->handWinner();

        return [
            'player_id' => $winner->id,
            'body' => "Won the hand with the {$winner->cardName()} of {$winner->cardSuitName()}s"
        ];
    }
}
