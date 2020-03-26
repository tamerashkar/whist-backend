<?php

namespace Tests\Unit\Listeners;

use App\Game;
use App\Player;
use Tests\TestCase;
use App\Events\GameHasStarted;
use App\Listeners\SelectDealer;
use App\Events\DealerWasSelected;
use Illuminate\Support\Facades\Event;
use App\Exceptions\RoundHasNotStarted;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SelectDealerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function sets_the_dealer_for_round()
    {
        Event::fake(DealerWasSelected::class);

        $game = Game::setup($players = factory(Player::class, 2)->create())->start();

        (new SelectDealer())->handle(new GameHasStarted($game));
        $this->assertSame($players->get(0)->id, $game->dealer()->id);

        (new SelectDealer())->handle(new GameHasStarted($game));
        $this->assertSame($players->get(1)->id, $game->dealer()->id);

        (new SelectDealer())->handle(new GameHasStarted($game));
        $this->assertSame($players->get(0)->id, $game->dealer()->id);

        Event::assertDispatched(DealerWasSelected::class, 3, function ($event) use ($game) {
            return $event->game->id === $game->id;
        });
    }

    /** @test */
    function throws_exception_when_setting_dealer_without_any_players()
    {
        Event::fake(DealerWasSelected::class);
        $this->expectException(RoundHasNotStarted::class);

        $game = Game::setup()->start();

        (new SelectDealer())->handle(new GameHasStarted($game));

        Event::assertNotDispatched(DealerWasSelected::class);
    }
}
