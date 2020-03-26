<?php

namespace Tests\Unit\Listeners;

use App\Game;
use App\Player;
use Tests\TestCase;
use App\Events\PlayerHasBid;
use App\Listeners\RequestBid;
use App\Events\BiddingHasEnded;
use App\Events\BidWasRequested;
use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RequestBidTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function dispatches_request_bid_when_last_bid_has_not_been_received()
    {
        Event::fake([BidWasRequested::class, BiddingHasEnded::class]);
        $game = Game::setup($players = factory(Player::class, 2)->create())
            ->start($players->first())
            ->receivedBidFrom($players->get(1), 7);

        (new RequestBid())->handle(new PlayerHasBid($game, $players->get(1), 7));

        Event::assertDispatched(BidWasRequested::class);
        Event::assertNotDispatched(BiddingHasEnded::class);
    }

    /** @test */
    function dispatches_bidding_has_ended_event_when_last_bid_has_been_received()
    {
        Event::fake([BidWasRequested::class, BiddingHasEnded::class]);
        $game = Game::setup($players = factory(Player::class, 2)
            ->create())
            ->start($players->first())
            ->receivedBidFrom($players->get(0), 7)
            ->receivedBidFrom($players->get(1), 0);

        (new RequestBid())->handle(new PlayerHasBid($game, $players->get(0), 0));

        Event::assertDispatched(BiddingHasEnded::class);
        Event::assertNotDispatched(BidWasRequested::class);
    }
}
