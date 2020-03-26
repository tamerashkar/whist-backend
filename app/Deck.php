<?php

namespace App;

class Deck
{
    protected static $suits = ['Heart', 'Spade', 'Diamond', 'Club'];
    protected static $values = [2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14];
    protected $cards = [];

    public function __construct($cards = [])
    {
        $this->cards = $cards;
    }

    public static function create()
    {
        $cards = [];
        foreach (static::$suits as $suit) {
            foreach (static::$values as $value) {
                $cards[] = new Card($suit, $value);
            }
        }

        return new static($cards);
    }

    public static function shuffled()
    {
        return new Deck(static::create()->shuffle()->cards());
    }

    public function shuffle()
    {
        shuffle($this->cards);
        return $this;
    }

    public function cards()
    {
        return $this->cards;
    }

    public function card($card)
    {
        return $this->cards[$card];
    }

    public function count()
    {
        return count($this->cards);
    }

    public function hasCards()
    {
        return !!$this->count();
    }

    public function deal()
    {
        return array_shift($this->cards);
    }
}
