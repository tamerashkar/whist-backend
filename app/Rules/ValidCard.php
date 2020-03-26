<?php

namespace App\Rules;

use App\Card;
use App\Game;
use Exception;
use App\Player;
use Illuminate\Contracts\Validation\Rule;

class ValidCard implements Rule
{
    protected $game;
    protected $player;
    protected $message = 'Card is invalid';

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
    public function passes($attribute, $card)
    {
        try {
            $card = new Card($card['suit'], $card['value']);
        } catch (Exception $e) {
            return false;
        }

        if (!$this->player->cardForGame($this->game, $card)->exists()) {
            $this->message = 'You do not have this card';
            return false;
        }

        if (
            $this->game->suit()
            && $card->suit() !== $this->game->suit()
            && $this->player->cardWithSuitForGame($this->game, $this->game->suit())->exists()
        ) {
            $this->message = "You must play a {$this->game->suit()} card";
            return false;
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return $this->message;
    }
}
