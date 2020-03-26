<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Game;
use App\Player;
use App\Message;
use Faker\Generator as Faker;

$factory->define(Message::class, function (Faker $faker) {
    return [
        'body' => $faker->text,
        'game_id' => function () {
            return factory(Game::class)->create()->id;
        },
        'player_id' => function () {
            return factory(Player::class)->create()->id;
        }
    ];
});
