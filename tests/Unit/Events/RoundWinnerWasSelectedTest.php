<?php

namespace Tests\Unit\Events;

use App\Card;
use App\Game;
use App\Player;
use Tests\TestCase;
use App\Events\RoundWinnerWasSelected;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RoundWinnerWasSelectedTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function creates_round_winner_message()
    {
        $game = Game::setup($players = factory(Player::class, 2)->create())->start($players->first());
        $this->makeBids($game, $players, [0, 7]);
        $game->receivedCardFrom($players->get(1), $players->get(1)->addCard($game, Card::heart(13)))
            ->receivedCardFrom($players->get(0), $players->get(0)->addCard($game, Card::heart(2)))
            ->selectRoundWinner();

        $message = (new RoundWinnerWasSelected($game))->message();

        $this->assertSame("Home won the round", $message['body']);
    }
}
