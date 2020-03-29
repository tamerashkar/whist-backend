<?php

namespace App\Events;

use App\Game;
use App\Player;

class PlayerHasPlayedCard extends GameEvent
{
    public $card;
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
            'game_id' => $this->game->id,
            'player_id' => $this->player->id,
            'body' => "Played the {$this->game->player($this->player)->first()->cardName()} of {$this->game->player($this->player)->first()->cardSuitName()}s"
        ];
    }
}
