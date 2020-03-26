<?php

namespace App\Rules;

use App\Game;
use Illuminate\Contracts\Validation\Rule;

class EmptyPosition implements Rule
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
        return $this->game->playersForTeam($value)->count() < Game::MAX_PLAYERS_PER_TEAM;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The team is full';
    }
}
