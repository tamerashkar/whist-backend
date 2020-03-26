<?php

namespace Tests\Unit\Events;

use App\Card;
use App\Game;
use App\Player;
use Tests\TestCase;
use App\Events\PlayerHasPlayedCard;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PlayerHasPlayedCardTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function creates_played_card_message()
    {
        $game = Game::setup($players = factory(Player::class, 4)->create())->start($players->first());

        $game->receivedCardFrom($players->get(1), $card = $players->get(1)->addCard($game, Card::heart(14)));

        $message = (new PlayerHasPlayedCard($game, $players->get(1)))->message();

        $this->assertSame($players->get(1)->id, $message['player_id']);
        $this->assertSame("Played the Ace of Hearts", $message['body']);
    }
}
