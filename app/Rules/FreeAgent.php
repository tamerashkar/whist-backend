<?php

namespace App\Rules;

use App\Game;
use App\Player;
use Illuminate\Contracts\Validation\Rule;

class FreeAgent implements Rule
{
    protected $game;
    protected $robot;
    protected $player;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(Game $game, Player $player, $robot = false)
    {
        $this->game = $game;
        $this->robot = $robot;
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
        return $this->robot || !$this->player->joined($this->game);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return  'You have already joined the game';
    }
}
