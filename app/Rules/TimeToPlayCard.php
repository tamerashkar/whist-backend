<?php

namespace App\Rules;

use App\Game;
use App\GameStatus;
use Illuminate\Contracts\Validation\Rule;

class TimeToPlayCard implements Rule
{
    protected $game;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(Game $game)
    {
        $this->game = $game;
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
        return $this->game->status === GameStatus::COMPETE;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return "It's not time to play a card";
    }
}
