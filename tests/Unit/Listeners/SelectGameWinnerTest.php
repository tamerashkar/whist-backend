<?php

namespace Tests\Unit\Listeners;

use App\Game;
use App\Player;
use Tests\TestCase;
use App\GameStatus;
use App\Events\GameHasEnded;
use App\Listeners\SelectGameWinner;
use App\Events\GameWinnerWasSelected;
use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SelectGameWinnerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function selects_the_round_winner()
    {
        Event::fake(GameWinnerWasSelected::class);
        $players = factory(Player::class, 4)->create();
        $game = Game::setup($players)->start($players->get(0));
        $game->pointsToWin(12);
        $this->makeBids($game, $players, [0, 7, 8, 8])
            ->playHands($game, $players, [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1]);
        $game->selectRoundWinner();

        (new SelectGameWinner())->handle(new GameHasEnded($game));

        $this->assertSame(GameStatus::WINNER, $game->fresh()->status);
        Event::assertDispatched(GameWinnerWasSelected::class, function ($event) use ($game) {
            return $event->game->id === $game->id;
        });
    }
}
