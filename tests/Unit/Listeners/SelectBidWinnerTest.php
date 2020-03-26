<?php

namespace Tests\Unit\Listeners;

use App\Game;
use App\Player;
use Tests\TestCase;
use App\Events\RoundHasEnded;
use App\Events\BiddingHasEnded;
use App\Listeners\SelectBidWinner;
use App\Events\BidWinnerWasSelected;
use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SelectBidWinnerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function selects_the_bid_winner_from_highest_bid()
    {
        Event::fake([RoundHasEnded::class, BidWinnerWasSelected::class]);
        $game = Game::setup($players = factory(Player::class, 2)->create())->start($players->get(0));
        $game->receivedBidFrom($players->get(1), 8);
        $game->receivedBidFrom($players->get(0), 0);

        (new SelectBidWinner())->handle(new BiddingHasEnded($game));

        $this->assertFalse((bool) $game->players()->get()->get(0)->pivot->bid_winner);
        $this->assertTrue((bool) $game->players()->get()->get(1)->pivot->bid_winner);
        Event::assertNotDispatched(RoundHasEnded::class);
        Event::assertDispatched(BidWinnerWasSelected::class, function ($event) use ($game) {
            return $event->game === $game;
        });
    }

    /** @test */
    function dealer_can_match_highest_bid()
    {
        Event::fake([RoundHasEnded::class, BidWinnerWasSelected::class]);
        $game = Game::setup($players = factory(Player::class, 2)->create())->start($players->get(0));
        $game->receivedBidFrom($players->get(1), 8);
        $game->receivedBidFrom($players->get(0), 8);

        (new SelectBidWinner())->handle(new BiddingHasEnded($game));

        $this->assertTrue((bool) $game->players()->get()->get(0)->pivot->bid_winner);
        $this->assertFalse((bool) $game->players()->get()->get(1)->pivot->bid_winner);
        Event::assertNotDispatched(RoundHasEnded::class);
        Event::assertDispatched(BidWinnerWasSelected::class, function ($event) use ($game) {
            return $event->game === $game;
        });
    }

    /** @test */
    function when_all_players_pass_round_has_ended()
    {
        Event::fake([RoundHasEnded::class, BidWinnerWasSelected::class]);
        $game = Game::setup($players = factory(Player::class, 2)->create())->start($players->get(0));
        $game->receivedBidFrom($players->get(1), 0);
        $game->receivedBidFrom($players->get(0), 0);

        (new SelectBidWinner())->handle(new BiddingHasEnded($game));

        $this->assertFalse((bool) $game->players()->get()->get(0)->pivot->bid_winner);
        $this->assertFalse((bool) $game->players()->get()->get(1)->pivot->bid_winner);
        Event::assertNotDispatched(BidWinnerWasSelected::class);
        Event::assertDispatched(RoundHasEnded::class);
    }
}
