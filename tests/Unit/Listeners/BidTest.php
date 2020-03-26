<?php

namespace Tests\Unit\Listeners;

use App\Game;
use App\Player;
use Tests\TestCase;
use App\Listeners\Bid;
use App\Events\PlayerHasBid;
use App\Events\BidWasRequested;
use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BidTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function makes_a_bid_when_player_is_a_robot()
    {
        Event::fake([PlayerHasBid::class]);
        $game = Game::setup($players = factory(Player::class, 2)->states('robot')->create())->start($players->get(0));

        (new Bid())->handle(new BidWasRequested($game));

        $this->assertNotNull($game->activePlayer()->pivot->bid);
        Event::assertDispatched(PlayerHasBid::class, function ($event) use ($game) {
            return $event->game === $game;
        });
    }

    /** @test */
    function does_not_make_a_bid_when_player_is_not_robot()
    {
        Event::fake([PlayerHasBid::class]);
        $game = Game::setup($players = factory(Player::class, 2)->create())->start($players->get(0));

        (new Bid())->handle(new BidWasRequested($game));

        $this->assertNull($game->activePlayer()->pivot->bid);
        Event::assertNotDispatched(PlayerHasBid::class);
    }
}
