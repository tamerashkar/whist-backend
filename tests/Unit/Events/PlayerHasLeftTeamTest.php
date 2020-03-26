<?php

namespace Tests\Unit\Events;

use App\Game;
use App\Player;
use Tests\TestCase;
use App\Events\PlayerHasLeftTeam;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PlayerHasLeftTeamTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function creates_player_has_left_home_team_message()
    {
        $game = Game::setup();
        $player = factory(Player::class)->create();
        $player->join($game, Game::HOME_TEAM);
        $player->leave($game);

        $message = (new PlayerHasLeftTeam($game, $player, Game::HOME_TEAM))->message();

        $this->assertSame("Left the home team", $message['body']);
    }

    /** @test */
    function creates_player_has_left_guest_team_message()
    {
        $game = Game::setup();
        $player = factory(Player::class)->create();
        $player->join($game, Game::GUEST_TEAM);
        $player->leave($game);

        $message = (new PlayerHasLeftTeam($game, $player, Game::GUEST_TEAM))->message();

        $this->assertSame("Left the guest team", $message['body']);
    }
}
