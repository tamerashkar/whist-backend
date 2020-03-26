<?php

namespace Tests\Unit\Rules;

use App\Game;
use App\Player;
use Tests\TestCase;
use App\Rules\EmptyPosition;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EmptyPositionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function returns_true_when_team_has_an_empty_position()
    {
        $game = Game::setup();
        factory(Player::class)->create()->join($game, Game::HOME_TEAM);

        $this->assertTrue((new EmptyPosition($game))->passes('team', Game::HOME_TEAM));
    }

    /** @test */
    function returns_false_when_team_does_not_have_an_empty_position()
    {
        $game = Game::setup();
        factory(Player::class)->create()->join($game, Game::HOME_TEAM);
        factory(Player::class)->create()->join($game, Game::HOME_TEAM);

        $this->assertFalse((new EmptyPosition($game))->passes('team', Game::HOME_TEAM));
    }
}
