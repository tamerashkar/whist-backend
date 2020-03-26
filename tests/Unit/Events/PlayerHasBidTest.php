<?php

namespace Tests\Unit\Events;

use App\Game;
use App\Player;
use Tests\TestCase;
use App\Events\PlayerHasBid;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PlayerHasBidTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function creates_bid_message()
    {
        $game = Game::setup($players = factory(Player::class, 4)->create())
            ->start($players->first())
            ->receivedBidFrom($players->get(1), 7);

        $message = (new PlayerHasBid($game, $players->get(1), 7))->message();

        $this->assertSame($players->get(1)->id, $message['player_id']);
        $this->assertSame("Bids 7", $message['body']);
    }

    /** @test */
    function creates_bid_message_when_player_has_passed()
    {
        $game = Game::setup($players = factory(Player::class, 4)->create())
            ->start($players->first())
            ->receivedBidFrom($players->get(1), 0);

        $message = (new PlayerHasBid($game, $players->get(1), 0))->message();

        $this->assertSame($players->get(1)->id, $message['player_id']);
        $this->assertSame("Passed", $message['body']);
    }
}
