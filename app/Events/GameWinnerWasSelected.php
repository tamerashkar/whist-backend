<?php

namespace App\Events;

class GameWinnerWasSelected extends GameEvent
{
    public function message(): array
    {
        $team = $this->game->winningTeamName();

        return [
            'body' => "{$team} won the game"
        ];
    }
}
