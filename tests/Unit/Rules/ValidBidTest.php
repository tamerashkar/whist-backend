<?php

namespace Tests\Unit\Rules;

use App\Game;
use App\Player;
use Tests\TestCase;
use App\Rules\ValidBid;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ValidBidTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function returns_false_when_bid_is_not_0_or_between_7_and_13()
    {
        $game = Game::setup([$player = factory(Player::class)->create()])->start($player);

        $this->assertFalse((new ValidBid($game, $player))->passes('bid', -1));
        $this->assertFalse((new ValidBid($game, $player))->passes('bid', 6));
        $this->assertFalse((new ValidBid($game, $player))->passes('bid', 14));
        $this->assertTrue((new ValidBid($game, $player))->passes('bid', 0));
        foreach (range(7, 13) as $bid) {
            $this->assertTrue((new ValidBid($game, $player))->passes('bid', $bid));
        }
    }

    /** @test */
    function returns_the_minimum_bid()
    {
        $player1 = factory(Player::class)->create();
        $player2 = factory(Player::class)->create();
        $player3 = factory(Player::class)->create();
        $player4 = factory(Player::class)->create();
        $game = Game::setup([$player1, $player2, $player3, $player4])->start($player1);
        $game->receivedBidFrom($player2, 7);

        $this->assertEquals(8, (new ValidBid($game, $player3))->minBid());
    }

    /** @test */
    function returns_the_minimum_bid_for_a_dealer()
    {
        $player1 = factory(Player::class)->create();
        $player2 = factory(Player::class)->create();
        $player3 = factory(Player::class)->create();
        $player4 = factory(Player::class)->create();
        $game = Game::setup([$player1, $player2, $player3, $player4])->start($player1);
        $game->receivedBidFrom($player2, 7);

        $this->assertEquals(7, (new ValidBid($game, $player1))->minBid());
    }

    /** @test */
    function returns_the_maximum_bid()
    {
        $game = Game::setup();
        $player = factory(Player::class)->create();

        $this->assertEquals(13, (new ValidBid($game, $player))->maxBid());
    }
}
