<?php

namespace App;

class Robot
{
    protected $player;

    public function __construct(Player $player)
    {
        $this->player = $player;
    }

    public function bidOn(Game $game)
    {
        $max = max($game->players()->pluck('bid')->toArray());
        $bid = $max >= 7 ? $max + 1 : 7;

        $game->receivedBidFrom($this->player, $bid);

        return $bid;
    }

    public function play(Game $game)
    {
        $suit = $game->suit();

        if ($suit && $this->canPlaySuit($game)) {
            $game->receivedCardFrom(
                $this->player,
                $game->player($this->player)
                    ->first()
                    ->cardsForGame($game)
                    ->where('suit', $game->suit())
                    ->inRandomOrder()
                    ->first()
            );
        } else if ($suit && $this->hasTrumpSuit($game)) {
            $game->receivedCardFrom(
                $this->player,
                $game->player($this->player)
                    ->first()
                    ->cardsForGame($game)
                    ->where('suit', $game->trumpSuit())
                    ->inRandomOrder()
                    ->first()
            );
        } else if ($suit) {
            // Throw away our lowest card
            $game->receivedCardFrom(
                $this->player,
                $game->player($this->player)
                    ->first()
                    ->cardsForGame($game)
                    ->orderBy('value')
                    ->first()
            );
        } else {
            // We are the first hand
            $game->receivedCardFrom(
                $this->player,
                $game->player($this->player)
                    ->first()
                    ->cardsForGame($game)
                    ->inRandomOrder()
                    ->first()
            );
        }
    }

    public function canPlaySuit($game)
    {
        return $this->player->cardsForGame($game)->where('suit', $game->suit())->exists();
    }

    public function hasTrumpSuit($game)
    {
        return $this->player->cardsForGame($game)->where('suit', $game->trumpSuit())->exists();
    }
}
