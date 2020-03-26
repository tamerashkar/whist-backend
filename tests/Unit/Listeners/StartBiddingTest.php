<?php

namespace Tests\Unit\Listeners;

use App\Game;
use App\Player;
use Tests\TestCase;
use App\GameStatus;
use App\Events\CardsWereDealt;
use App\Listeners\StartBidding;
use App\Events\BiddingHasStarted;
use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StartBiddingTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function starts_bidding()
    {
        Event::fake(BiddingHasStarted::class);
        $game = Game::setup(factory(Player::class, 2)->create())->start();

        (new StartBidding())->handle(new CardsWereDealt($game));

        $this->assertSame(GameStatus::BIDDING, $game->status);
        Event::assertDispatched(BiddingHasStarted::class, function ($event) use ($game) {
            return $event->game->id === $game->id;
        });
    }
}
