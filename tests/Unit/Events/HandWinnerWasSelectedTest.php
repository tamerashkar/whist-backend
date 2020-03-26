<?php

namespace Tests\Unit\Events;

use App\Card;
use App\Game;
use App\Player;
use Tests\TestCase;
use App\Events\HandWinnerWasSelected;
use Illuminate\Foundation\Testing\RefreshDatabase;

class HandWinnerWasSelectedTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function creates_hand_winner_message()
    {
        $game = Game::setup($players = factory(Player::class, 2)->create())->start($players->first());
        $game->receivedCardFrom($players->get(1), $players->get(1)->addCard($game, Card::heart(13)))
            ->receivedCardFrom($players->get(0), $players->get(0)->addCard($game, Card::heart(2)))
            ->selectHandWinner();

        $message = (new HandWinnerWasSelected($game))->message();

        $this->assertSame("Won the hand with the King of Hearts", $message['body']);
    }
}
