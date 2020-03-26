<?php

namespace Tests\Unit\Rules;

use App\Game;
use App\Player;
use Tests\TestCase;
use App\Rules\Turn;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TurnTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function returns_false_when_it_is_not_my_turn()
    {
        $player1 = factory(Player::class)->create();
        $player2 = factory(Player::class)->create();
        $player3 = factory(Player::class)->create();
        $player4 = factory(Player::class)->create();
        $game = Game::setup([$player1, $player2, $player3, $player4])->start($player1);

        $this->assertFalse((new Turn($game, $player2))->passes('bid', 7));
    }

    /** @test */
    function returns_true_when_it_is_my_turn()
    {
        $player1 = factory(Player::class)->create();
        $player2 = factory(Player::class)->create();
        $player3 = factory(Player::class)->create();
        $player4 = factory(Player::class)->create();
        $game = Game::setup([$player1, $player2, $player3, $player4])->start($player1);
        $game->nextTurn();
        $game->receivedBidFrom($player2, 7);
        $game->nextTurn();

        $this->assertTrue((new Turn($game, $player3))->passes('bid', 7));
    }
}
