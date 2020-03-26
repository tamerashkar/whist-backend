<?php

namespace Tests\Unit\Events;

use App\Game;
use App\Player;
use Tests\TestCase;
use App\Events\PlayerHasJoinedTeam;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PlayerHasJoinedTeamTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function creates_player_has_joined_home_team_message()
    {
        $game = Game::setup();
        $player = factory(Player::class)->create();
        $player->join($game, Game::HOME_TEAM);

        $message = (new PlayerHasJoinedTeam($game, $player, Game::HOME_TEAM))->message();

        $this->assertSame("Joined the home team", $message['body']);
    }

    /** @test */
    function creates_player_has_joined_guest_team_message()
    {
        $game = Game::setup();
        $player = factory(Player::class)->create();
        $player->join($game, Game::GUEST_TEAM);

        $message = (new PlayerHasJoinedTeam($game, $player, Game::GUEST_TEAM))->message();

        $this->assertSame("Joined the guest team", $message['body']);
    }
}
