<?php

namespace Tests\Unit\Listeners;

use App\Game;
use App\Player;
use Tests\TestCase;
use App\Events\RoundHasEnded;
use App\Listeners\SelectRoundWinner;
use App\Events\RoundWinnerWasSelected;
use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SelectRoundWinnerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function selects_the_round_winner()
    {
        Event::fake(RoundWinnerWasSelected::class);
        $players = factory(Player::class, 4)->create();
        $game = Game::setup($players)->start($players->get(0));
        $this->makeBids($game, $players, [0, 7, 8, 8])
            ->playHands($game, $players, [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1]);

        (new SelectRoundWinner())->handle(new RoundHasEnded($game));

        $round = $game->round();
        $this->assertSame($players->get(2)->id, $round->bid_winner);
        $this->assertSame(8, $round->bid);
        $this->assertSame(12, $round->home_team_points);
        $this->assertSame(0, $round->guest_team_points);
        $this->assertSame(12, $round->home_team_points);
        $this->assertSame(0, $round->guest_team_points);
        Event::assertDispatched(RoundWinnerWasSelected::class, function ($event) use ($game) {
            return $event->game->id === $game->id;
        });
    }
}
