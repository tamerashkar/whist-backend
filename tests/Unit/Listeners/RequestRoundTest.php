<?php

namespace Tests\Unit\Listeners;

use App\Game;
use App\Player;
use Tests\TestCase;
use App\Events\GameHasEnded;
use App\Listeners\RequestRound;
use App\Events\RoundWasRequested;
use App\Events\RoundWinnerWasSelected;
use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RequestRoundTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function dispatches_round_was_requested_when_no_team_has_won()
    {
        Event::fake([RoundWasRequested::class, GameHasEnded::class]);

        $game = Game::setup($players = factory(Player::class, 4)->create())
            ->start($players->first())
            ->pointsToWin(52);

        $this
            ->makeBids($game, $players, [0, 7, 8, 8])
            ->playHands($game, $players, [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1]);

        $game->selectRoundWinner();

        (new RequestRound())->handle(new RoundWinnerWasSelected($game));

        Event::assertDispatched(RoundWasRequested::class);
        Event::assertNotDispatched(GameHasEnded::class);
    }

    /** @test */
    function dispatches_game_has_ended_when_a_team_has_won()
    {
        Event::fake([RoundWasRequested::class, GameHasEnded::class]);

        $game = Game::setup($players = factory(Player::class, 2)->create())->start($players->first());

        $game = Game::setup($players = factory(Player::class, 4)->create())
            ->start($players->first())
            ->pointsToWin(8);

        $this
            ->makeBids($game, $players, [0, 7, 8, 8])
            ->playHands($game, $players, [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1]);

        $game->selectRoundWinner();

        (new RequestRound())->handle(new RoundWinnerWasSelected($game));

        Event::assertNotDispatched(RoundWasRequested::class);
        Event::assertDispatched(GameHasEnded::class);
    }
}
