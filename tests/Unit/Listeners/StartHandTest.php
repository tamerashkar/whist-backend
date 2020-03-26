<?php

namespace Tests\Unit\Listeners;

use App\Game;
use App\Player;
use Tests\TestCase;
use App\GameStatus;
use App\Listeners\StartHand;
use App\Events\HandHasStarted;
use App\Events\BiddingHasEnded;
use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StartHandTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function starts_hand()
    {
        Event::fake(HandHasStarted::class);
        $game = Game::setup(factory(Player::class, 2)->create())->start();

        (new StartHand())->handle(new BiddingHasEnded($game));

        $this->assertSame(GameStatus::COMPETE, $game->fresh()->status);
        Event::assertDispatched(HandHasStarted::class, function ($event) use ($game) {
            return $event->game->id === $game->id;
        });
    }
}
