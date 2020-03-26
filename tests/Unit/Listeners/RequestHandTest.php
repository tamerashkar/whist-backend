<?php

namespace Tests\Unit\Listeners;

use App\Card;
use App\Game;
use App\Player;
use Tests\TestCase;
use App\Events\RoundHasEnded;
use App\Listeners\RequestHand;
use App\Events\HandWasRequested;
use App\Events\HandWinnerWasSelected;
use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RequestHandTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function dispatches_hand_was_requested_when_all_hands_have_not_been_played()
    {
        Event::fake([HandWasRequested::class, RoundHasEnded::class]);

        $game = Game::setup($players = factory(Player::class, 2)->create())->start($players->first());

        $game->receivedCardFrom(
            $players->get(0),
            $players->get(0)->addCard($game, Card::heart(2))
        );
        $game->receivedCardFrom(
            $players->get(1),
            $players->get(1)->addCard($game, Card::heart(3))
        );
        $players->get(0)->addCard($game, Card::heart(4));
        $players->get(1)->addCard($game, Card::heart(5));
        $game->selectHandWinner();

        (new RequestHand())->handle(new HandWinnerWasSelected($game));

        Event::assertDispatched(HandWasRequested::class);
        Event::assertNotDispatched(RoundHasEnded::class);
    }

    /** @test */
    function dispatches_round_has_ended_when_all_hands_have_been_played()
    {
        Event::fake([HandWasRequested::class, RoundHasEnded::class]);

        $game = Game::setup($players = factory(Player::class, 2)->create())->start($players->first());

        $game->receivedCardFrom(
            $players->get(0),
            $players->get(0)->addCard($game, Card::heart(2))
        );
        $game->receivedCardFrom(
            $players->get(1),
            $players->get(1)->addCard($game, Card::heart(3))
        );
        $game->selectHandWinner();

        (new RequestHand())->handle(new HandWinnerWasSelected($game));

        Event::assertNotDispatched(HandWasRequested::class);
        Event::assertDispatched(RoundHasEnded::class);
    }
}
