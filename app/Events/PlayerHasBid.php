<?php

namespace App\Events;

use App\Game;
use App\Player;

class PlayerHasBid extends GameEvent implements Announceable
{
    public $bid;
    public $game;
    public $player;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Game $game, Player $player, $bid)
    {
        $this->bid = $bid;
        $this->player = $player;
        parent::__construct($game);
    }

    public function message(): array
    {
        return [
            'player_id' => $this->player->id,
            'body' => $this->bid === 0 ? "Passed" : "Bids {$this->bid}"
        ];
    }
}
