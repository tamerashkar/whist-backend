<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\User;
use App\Player;
use Faker\Generator as Faker;

$factory->define(Player::class, function (Faker $faker) {
    return [
        'name' => $faker->firstName(),
        'robot' => false,
        'user_id' => function () {
            return factory(User::class)->create()->id;
        }
    ];
});

$factory->state(Player::class, 'robot', function (Faker $faker) {
    return [
        'robot' => true
    ];
});
