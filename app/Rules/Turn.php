<?php

namespace App\Rules;

use App\Game;
use App\Player;
use Illuminate\Contracts\Validation\Rule;

class Turn implements Rule
{
    protected $game;
    protected $player;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(Game $game, Player $player)
    {
        $this->game = $game;
        $this->player = $player;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $active = $this->game->activePlayer();

        return $active && $active->id === $this->player->id;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return "It's not your turn";
    }
}
