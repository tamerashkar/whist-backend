<?php

namespace Tests\Unit\Rules;

use App\Game;
use App\Player;
use Tests\TestCase;
use App\Rules\FreeAgent;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FreeAgentTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function returns_true_when_player_is_not_part_of_the_game()
    {
        $game = Game::setup();
        $player = factory(Player::class)->create();

        $this->assertTrue((new FreeAgent($game, $player))->passes('team', Game::HOME_TEAM));
    }

    /** @test */
    function returns_false_when_player_is_part_of_the_game()
    {
        $game = Game::setup();
        $player = factory(Player::class)->create();
        $player->join($game, Game::HOME_TEAM);

        $this->assertFalse((new FreeAgent($game, $player))->passes('team', Game::HOME_TEAM));
    }
}
