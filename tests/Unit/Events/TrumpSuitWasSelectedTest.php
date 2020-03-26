<?php

namespace Tests\Unit\Events;

use App\Card;
use Tests\TestCase;
use App\Events\TrumpSuitWasSelected;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TrumpSuitWasSelectedTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function creates_trump_suit_was_selected_message()
    {
        $players = $this->createPlayers(4);
        $game = $this->startGame($players);
        $this->makeBids($game, $players, [0, 0, 0, 7]);
        $game->setStartTurn(1);
        $game->setNextTurn(2);
        $game->receivedCardFrom($players->get(3), $players->get(3)->addCard($game, Card::heart(12)));
        $game->receivedCardFrom($players->get(0), $players->get(3)->addCard($game, Card::spade(12)));
        $game->receivedCardFrom($players->get(1), $players->get(3)->addCard($game, Card::club(12)));
        $game->receivedCardFrom($players->get(2), $players->get(3)->addCard($game, Card::diamond(12)));

        $message = (new TrumpSuitWasSelected($game))->message();

        $this->assertSame("Selected Spade as the trump card", $message['body']);
    }
}
