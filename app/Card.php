<?php

namespace App;

use InvalidArgumentException;

class Card
{
    const CLUB = 'Club';
    const HEART = 'Heart';
    const SPADE = 'Spade';
    const DIAMOND = 'Diamond';

    protected $suit;
    protected $value;

    public function __construct(string $suit, int $value)
    {
        if ($value < 2 || $value > 14) {
            throw new InvalidArgumentException("Invalid card value [{$value}]");
        }

        if (array_search($suit, static::suits()) === false) {
            throw new InvalidArgumentException("Invalid card suit [{$suit}]");
        }

        $this->suit = $suit;
        $this->value = $value;
    }

    public static function suits()
    {
        return [static::CLUB, static::HEART, static::SPADE, static::DIAMOND];
    }

    public static function club(int $value)
    {
        return new Card(static::CLUB, $value);
    }

    public static function heart(int $value)
    {
        return new Card(static::HEART, $value);
    }

    public static function spade(int $value)
    {
        return new Card(static::SPADE, $value);
    }

    public static function diamond(int $value)
    {
        return new Card(static::DIAMOND, $value);
    }

    public function suit()
    {
        return $this->suit;
    }

    public function value()
    {
        return $this->value;
    }

    public function toArray()
    {
        return ['suit' => $this->suit(), 'value' => $this->value()];
    }

    public static function name($value)
    {
        $names = [
            11 => 'Jack',
            12 => 'Queen',
            13 => 'King',
            14 => 'Ace',
        ];

        return isset($names[$value]) ? $names[$value] : $value;
    }

    public static function suitName($suit)
    {
        return $suit;
    }
}
