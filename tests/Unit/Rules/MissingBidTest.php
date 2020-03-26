<?php

namespace Tests\Unit\Rules;

use App\Game;
use App\Player;
use Tests\TestCase;
use App\Rules\MissingBid;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MissingBidTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function returns_true_when_player_has_not_bid()
    {
        $game = Game::setup();
        $player = factory(Player::class)->create();
        $player->join($game, Game::HOME_TEAM);

        $this->assertTrue((new MissingBid($game, $player))->passes('bid', 7));
    }

    /** @test */
    function returns_false_when_player_has_already_bid()
    {
        $game = Game::setup();
        $player = factory(Player::class)->create();
        $player->join($game, Game::HOME_TEAM);
        $game->receivedBidFrom($player, 0);

        $this->assertFalse((new MissingBid($game, $player))->passes('bid', 0));
    }
}
