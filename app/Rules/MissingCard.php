<?php

namespace App\Rules;

use App\Game;
use App\Player;
use Illuminate\Contracts\Validation\Rule;

class MissingCard implements Rule
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
        return !$this->player->hasPlayedCard($this->game);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'You have already played a card';
    }
}
