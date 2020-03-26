<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Game;
use App\GameStatus;
use Faker\Generator as Faker;

$factory->define(Game::class, function (Faker $faker) {
    return [
        'next_turn' => 1,
        'start_turn' => 1,
        'points_to_win' => 52,
        'status' => GameStatus::TEAM_SELECTION
    ];
});
