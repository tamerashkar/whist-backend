<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Player extends Model
{
    protected $guarded = [
        'id'
    ];

    public $casts = [
        'id' => 'string',
        'position' => 'int',
        'robot' => 'boolean'
    ];

    public function isRobot()
    {
        return $this->robot;
    }

    public function robot()
    {
        return new Robot($this);
    }

    public function games()
    {
        return $this->belongsToMany(Game::class)->withPivot(GamePlayer::$pivots);
    }

    public function game(Game $game)
    {
        return $this->games()->where('game_id', $game->id)->first();
    }

    public function pivot()
    {
        return $this->hasOne(GamePlayer::class);
    }

    public function cards()
    {
        return $this->hasMany(PlayerCard::class);
    }

    public function cardsForGame(Game $game)
    {
        return $this->cards()->where('game_id', $game->id);
    }

    public function cardForGame(Game $game, Card $card)
    {
        return $this->cardsForGame($game)
            ->where('suit', $card->suit())
            ->where('value', $card->value());
    }

    public function cardWithSuitForGame(Game $game, string $suit)
    {
        return $this->cardsForGame($game)->where('suit', $suit);
    }


    public function join(Game $game, $team)
    {
        $game->players()->attach($this->id, [
            'team' => $team,
            'position' => $game->playersForTeam($team)->count() * 2 + 1 * $team,
        ]);
        return $this;
    }

    public function joined(Game $game)
    {
        return !!$this->game($game);
    }

    public function hasCard(Game $game, Card $card)
    {
        return $this->cardForGame($game, $card)->exists();
    }

    public function addCard(Game $game, Card $card)
    {
        $params = [
            'suit' => $card->suit(),
            'value' => $card->value(),
            'game_id' => $game->id,
        ];

        return $this->cards()->updateOrCreate($params, $params);
    }

    public function cardName()
    {
        return $this->pivot->value ? Card::name($this->pivot->value) : '';
    }

    public function cardSuitName()
    {
        return $this->pivot->suit ? Card::suitName($this->pivot->suit) : '';
    }

    public function hasBid(Game $game)
    {
        return $this->pivot()
            ->where('game_id', $game->id)
            ->whereNotNull('bid')
            ->exists();
    }

    public function hasPlayedCard(Game $game)
    {
        return $this->pivot()
            ->where('game_id', $game->id)
            ->whereNotNull('suit')
            ->exists();
    }
}
