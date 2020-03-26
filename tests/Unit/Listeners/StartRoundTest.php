<?php

namespace Tests\Unit\Listeners;

use App\Game;
use App\Player;
use Tests\TestCase;
use App\GameStatus;
use App\Listeners\StartRound;
use App\Events\RoundHasStarted;
use App\Events\RoundWasRequested;
use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StartRoundTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function starts_bidding()
    {
        Event::fake(RoundHasStarted::class);
        $game = Game::setup(factory(Player::class, 2)->create())->start();

        (new StartRound())->handle(new RoundWasRequested($game));

        $this->assertSame(GameStatus::ROUND, $game->status);
        Event::assertDispatched(RoundHasStarted::class, function ($event) use ($game) {
            return $event->game->id === $game->id;
        });
    }
}
