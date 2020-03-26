<?php

namespace App\Events;

use App\Game;
use App\Player;

class PlayerHasJoinedLobby extends GameEvent implements Announceable
{
    public $player;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Game $game, Player $player)
    {
        $this->player = $player;
        parent::__construct($game);
    }

    public function message(): array
    {
        return [
            'player_id' => $this->player->id,
            'body' => "{$this->player->name} has joined the lobby"
        ];
    }
}
