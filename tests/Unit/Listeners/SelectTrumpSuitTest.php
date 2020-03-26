<?php

namespace Tests\Unit\Listeners;

use App\Card;
use Tests\TestCase;
use App\Listeners\SelectTrumpSuit;
use App\Events\PlayerHasPlayedCard;
use App\Events\TrumpSuitWasSelected;
use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SelectTrumpSuitTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function selects_the_trump_suit()
    {
        Event::fake(TrumpSuitWasSelected::class);
        $players = $this->createPlayers(4);
        $game = $this->startGame($players);
        $this->makeBids($game, $players, [0, 0, 0, 7]);
        $game->setStartTurn(1);
        $game->setNextTurn(2);
        $game->receivedCardFrom($players->get(3), $players->get(3)->addCard($game, Card::heart(12)));
        $game->receivedCardFrom($players->get(0), $players->get(3)->addCard($game, Card::spade(12)));
        $game->receivedCardFrom($players->get(1), $players->get(3)->addCard($game, Card::club(12)));
        $game->receivedCardFrom($players->get(2), $players->get(3)->addCard($game, Card::diamond(12)));

        (new SelectTrumpSuit($game))->handle(new PlayerHasPlayedCard($game, $players->get(3)));

        $this->assertSame(Card::SPADE, $game->trumpSuit());
        Event::assertDispatched(TrumpSuitWasSelected::class);
    }

    /** @test */
    function does_not_reselect_the_trump_suit_when_it_has_been_selected()
    {
        Event::fake(TrumpSuitWasSelected::class);
        $players = $this->createPlayers(4);
        $game = $this->startGame($players);
        $this->makeBids($game, $players, [0, 0, 0, 7]);
        $game->setStartTurn(1);
        $game->setNextTurn(2);
        $game->receivedCardFrom($players->get(3), $players->get(3)->addCard($game, Card::heart(12)));
        $game->receivedCardFrom($players->get(0), $players->get(3)->addCard($game, Card::spade(12)));
        $game->receivedCardFrom($players->get(1), $players->get(3)->addCard($game, Card::club(12)));
        $game->receivedCardFrom($players->get(2), $players->get(3)->addCard($game, Card::diamond(12)));
        $game->selectTrumpSuit();
        $game->receivedCardFrom($players->get(3), $players->get(3)->addCard($game, Card::heart(12)));
        $game->receivedCardFrom($players->get(0), $players->get(3)->addCard($game, Card::heart(12)));
        $game->receivedCardFrom($players->get(1), $players->get(3)->addCard($game, Card::heart(12)));
        $game->receivedCardFrom($players->get(2), $players->get(3)->addCard($game, Card::heart(12)));

        (new SelectTrumpSuit($game))->handle(new PlayerHasPlayedCard($game, $players->get(3)));

        $this->assertSame(Card::SPADE, $game->trumpSuit());
        Event::assertNotDispatched(TrumpSuitWasSelected::class);
    }
}
