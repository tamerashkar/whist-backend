<?php

namespace App\Events;

class RoundWinnerWasSelected extends GameEvent
{
    public function message(): array
    {
        $team = $this->game->round()->winningTeamName();

        return [
            'body' => "${team} won the round"
        ];
    }
}
