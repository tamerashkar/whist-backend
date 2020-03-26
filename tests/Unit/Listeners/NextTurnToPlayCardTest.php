<?php

namespace Tests\Unit\Listeners;

use App\Deck;
use App\Game;
use App\Player;
use Tests\TestCase;
use App\Events\PlayerHasPlayedCard;
use App\Listeners\NextTurnToPlayCard;
use Illuminate\Foundation\Testing\RefreshDatabase;

class NextTurnToPlayCardTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function moves_to_next_turn_from_previous_turn()
    {
        $game = Game::setup($players = factory(Player::class, 2)
            ->create())
            ->start($players->first())
            ->deal(Deck::create())
            ->compete();
        $game->receivedCardFrom($players->get(0), $card = $players->get(0)->cardsForGame($game)->first());

        (new NextTurnToPlayCard())->handle(new PlayerHasPlayedCard($game, $players->get(0), $card));

        $this->assertEquals(2, $game->fresh()->next_turn);
    }

    /** @test */
    function does_not_move_turns_when_we_have_received_all_cards()
    {
        $game = Game::setup($players = factory(Player::class, 2)
            ->create())
            ->start($players->first())
            ->deal(Deck::create())
            ->compete();
        $game->receivedCardFrom($players->get(0), $card = $players->get(0)->cardsForGame($game)->first());
        $game->nextTurn();
        $game->receivedCardFrom($players->get(1), $card = $players->get(1)->cardsForGame($game)->first());
        $this->assertEquals(2, $game->fresh()->next_turn);

        (new NextTurnToPlayCard())->handle(new PlayerHasPlayedCard($game, $players->get(1), $card));

        $this->assertEquals(2, $game->fresh()->next_turn);
    }
}
