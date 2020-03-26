<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GameRound extends Model
{
    protected $guarded = [
        'id'
    ];

    public $casts = [
        'bid' => 'int',
        'home_team_points' => 'int',
        'guest_team_points' => 'int'
    ];

    public function winningTeamName()
    {
        return $this->home_team_points > $this->guest_team_points ? Game::HOME_TEAM_NAME : Game::GUEST_TEAM_NAME;
    }
}
