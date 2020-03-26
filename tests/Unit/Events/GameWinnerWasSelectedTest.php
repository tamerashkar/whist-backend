<?php

namespace Tests\Unit\Events;

use App\Game;
use App\Player;
use Tests\TestCase;
use App\Events\GameWinnerWasSelected;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GameWinnerWasSelectedTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function creates_game_winner_message()
    {
        $players = factory(Player::class, 4)->create();
        $game = Game::setup($players)->start($players->get(0));
        $game->pointsToWin(12);
        $this->makeBids($game, $players, [0, 7, 8, 8])
            ->playHands($game, $players, [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1]);
        $game->selectRoundWinner();
        $game->selectGameWinner();

        $message = (new GameWinnerWasSelected($game))->message();

        $this->assertSame('Home won the game', $message['body']);
    }
}
