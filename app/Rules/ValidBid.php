<?php

namespace App\Rules;

use App\Game;
use App\Player;
use Illuminate\Contracts\Validation\Rule;

class ValidBid implements Rule
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
        return $this->isPassingBid($value) || $this->between($value, $this->minBid(), $this->maxBid());
    }

    public function isPassingBid($value)
    {
        return $value === 0;
    }

    public function between($value, $start, $end)
    {
        return $start <= $value && $value <= $end;
    }

    public function minBid()
    {
        // @todo(tamer) - how to handle situations where a player has already bid 13?
        $max = $this->game->players()->max('bid');

        if (!$max) {
            return 7;
        }

        return $this->game->dealer()->id === $this->player->id ? $max : $max + 1;
    }

    public function maxBid()
    {
        return 13;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return "The bid must be between {$this->minBid()} and {$this->maxBid()}";
    }
}
