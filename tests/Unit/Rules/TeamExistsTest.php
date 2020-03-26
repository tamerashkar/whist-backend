<?php

namespace Tests\Unit\Rules;

use App\Game;
use Tests\TestCase;
use App\Rules\TeamExists;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TeamExistsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function returns_true_when_team_exists()
    {
        $this->assertTrue((new TeamExists(Game::setup()))->passes('team', Game::HOME_TEAM));
    }

    /** @test */
    function returns_false_when_team_does_not_exist()
    {
        $this->assertFalse((new TeamExists(Game::setup()))->passes('team', 100));
    }
}
