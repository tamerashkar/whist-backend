<?php

namespace App;

class Robot
{
    protected static $names = [
        'Allison',
        'Arthur',
        'Ana',
        'Alex',
        'Arlene',
        'Alberto',
        'Barry',
        'Bertha',
        'Bill',
        'Bonnie',
        'Bret',
        'Beryl',
        'Chantal',
        'Cristobal',
        'Claudette',
        'Charley',
        'Cindy',
        'Chris',
        'Dean',
        'Dolly',
        'Danny',
        'Danielle',
        'Dennis',
        'Debby',
        'Erin',
        'Edouard',
        'Erika',
        'Earl',
        'Emily',
        'Ernesto',
        'Felix',
        'Fay',
        'Fabian',
        'Frances',
        'Franklin',
        'Florence',
        'Gabielle',
        'Gustav',
        'Grace',
        'Gaston',
        'Gert',
        'Gordon',
        'Humberto',
        'Hanna',
        'Henri',
        'Hermine',
        'Harvey',
        'Helene',
        'Iris',
        'Isidore',
        'Isabel',
        'Ivan',
        'Irene',
        'Isaac',
        'Jerry',
        'Josephine',
        'Juan',
        'Jeanne',
        'Jose',
        'Joyce',
        'Karen',
        'Kyle',
        'Kate',
        'Karl',
        'Katrina',
        'Kirk',
        'Lorenzo',
        'Lili',
        'Larry',
        'Lisa',
        'Lee',
        'Leslie',
        'Michelle',
        'Marco',
        'Mindy',
        'Maria',
        'Michael',
        'Noel',
        'Nana',
        'Nicholas',
        'Nicole',
        'Nate',
        'Nadine',
        'Olga',
        'Omar',
        'Odette',
        'Otto',
        'Ophelia',
        'Oscar',
        'Pablo',
        'Paloma',
        'Peter',
        'Paula',
        'Philippe',
        'Patty',
        'Rebekah',
        'Rene',
        'Rose',
        'Richard',
        'Rita',
        'Rafael',
        'Sebastien',
        'Sally',
        'Sam',
        'Shary',
        'Stan',
        'Sandy',
        'Tanya',
        'Teddy',
        'Teresa',
        'Tomas',
        'Tammy',
        'Tony',
        'Van',
        'Vicky',
        'Victor',
        'Virginie',
        'Vince',
        'Valerie',
        'Wendy',
        'Wilfred',
        'Wanda',
        'Walter',
        'Wilma',
        'William',
        'Kumiko',
        'Aki',
        'Miharu',
        'Chiaki',
        'Michiyo',
        'Itoe',
        'Nanaho',
        'Reina',
        'Emi',
        'Yumi',
        'Ayumi',
        'Kaori',
        'Sayuri',
        'Rie',
        'Miyuki',
        'Hitomi',
        'Naoko',
        'Miwa',
        'Etsuko',
        'Akane',
        'Kazuko',
        'Miyako',
        'Youko',
        'Sachiko',
        'Mieko',
        'Toshie',
        'Junko'
    ];

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

    public static function randomName()
    {
        return static::$names[array_rand(static::$names)];
    }
}
