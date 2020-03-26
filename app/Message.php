<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $guarded = [
        'id'
    ];

    public function game()
    {
        return $this->belongsTo(Game::class);
    }

    public function player()
    {
        return $this->belongsTo(Player::class);
    }

    public static function eagerLoadsFor(Game $game)
    {
        return [
            'player.pivot' => function ($query) use ($game) {
                return $query->where('game_id', $game->id);
            }
        ];
    }
}
