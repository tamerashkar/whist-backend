<?php

namespace App\Events;

use App\Game;
use App\Player;

class PlayerHasLeftTeam extends GameEvent implements Announceable
{
    public $team;
    public $player;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Game $game, Player $player, $team)
    {
        $this->team = $team;
        $this->player = $player;
        parent::__construct($game);
    }

    public function message(): array
    {
        $team = strtolower($this->team == Game::HOME_TEAM ? Game::HOME_TEAM_NAME : Game::GUEST_TEAM_NAME);

        return [
            'player_id' => $this->player->id,
            'body' => "Left the {$team} team"
        ];
    }
}
