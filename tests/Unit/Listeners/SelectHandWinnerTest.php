<?php

namespace Tests\Unit\Listeners;

use App\Card;
use App\Game;
use App\Player;
use Tests\TestCase;
use App\Events\HandHasEnded;
use App\Listeners\SelectHandWinner;
use App\Events\HandWinnerWasSelected;
use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SelectHandWinnerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function selects_the_hand_winner()
    {
        Event::fake(HandWinnerWasSelected::class);
        $game = Game::setup($players = factory(Player::class, 2)->create())->start($players->get(0));
        $game->receivedCardFrom($players->get(1), $players->get(1)->addCard($game, Card::heart(13)));
        $game->receivedCardFrom($players->get(0), $players->get(0)->addCard($game, Card::heart(2)));

        (new SelectHandWinner())->handle(new HandHasEnded($game));

        $this->assertFalse((bool) $game->players()->get()->get(0)->pivot->hand_winner);
        $this->assertTrue((bool) $game->players()->get()->get(1)->pivot->hand_winner);
        Event::assertDispatched(HandWinnerWasSelected::class, function ($event) use ($game) {
            return $event->game->id === $game->id;
        });
    }
}
