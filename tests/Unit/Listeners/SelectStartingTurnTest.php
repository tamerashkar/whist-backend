<?php

namespace Tests\Unit\Listeners;

use App\Card;
use App\Game;
use App\Player;
use Tests\TestCase;
use App\Events\HandWasRequested;
use App\Events\DealerWasSelected;
use App\Events\BidWinnerWasSelected;
use App\Events\HandWinnerWasSelected;
use App\Listeners\SelectStartingTurn;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SelectStartingTurnTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function sets_starting_turn_when_a_dealer_was_selected()
    {
        $game = Game::setup($players = factory(Player::class, 4)->create())
            ->start()
            ->setDealer($players->get(0));

        (new SelectStartingTurn())->handle(new DealerWasSelected($game));

        $this->assertEquals(2, $game->fresh()->next_turn);
        $this->assertEquals(2, $game->fresh()->start_turn);
    }

    /** @test */
    function sets_starting_turn_when_bid_winner_was_selected()
    {
        $game = Game::setup($players = factory(Player::class, 4)->create())
            ->start()
            ->setDealer($players->get(0))
            ->receivedBidFrom($players->get(1), 8)
            ->receivedBidFrom($players->get(2), 0)
            ->receivedBidFrom($players->get(3), 9)
            ->receivedBidFrom($players->get(0), 0)
            ->selectBidWinner();

        (new SelectStartingTurn())->handle(new BidWinnerWasSelected($game));

        $this->assertEquals(4, $game->fresh()->next_turn);
        $this->assertEquals(4, $game->fresh()->start_turn);
    }

    /** @test */
    function sets_starting_turn_when_hand_winner_was_selected()
    {
        $game = Game::setup($players = factory(Player::class, 2)->create())
            ->start()
            ->setDealer($players->get(0));

        $game->receivedCardFrom($players->get(1), $players->get(1)->addCard($game, Card::heart(13)))
            ->receivedCardFrom($players->get(0), $players->get(0)->addCard($game, Card::heart(2)))
            ->selectHandWinner();

        (new SelectStartingTurn())->handle(new HandWinnerWasSelected($game));

        $this->assertEquals(2, $game->fresh()->next_turn);
        $this->assertEquals(2, $game->fresh()->start_turn);
    }

    /** @test */
    function sets_starting_turn_when_hand_has_started_coming_from_bidding_round()
    {
        $game = Game::setup($players = factory(Player::class, 4)->create())
            ->start()
            ->setDealer($players->get(0))
            ->receivedBidFrom($players->get(1), 8)
            ->receivedBidFrom($players->get(2), 0)
            ->receivedBidFrom($players->get(3), 9)
            ->receivedBidFrom($players->get(0), 0)
            ->selectBidWinner();

        (new SelectStartingTurn())->handle(new HandWasRequested($game));

        $this->assertEquals(4, $game->fresh()->next_turn);
        $this->assertEquals(4, $game->fresh()->start_turn);
    }

    /** @test */
    function sets_starting_turn_when_hand_has_started_coming_from_previous_hand()
    {
        $game = Game::setup($players = factory(Player::class, 2)->create())
            ->start()
            ->setDealer($players->get(0));

        $game->receivedCardFrom($players->get(1), $players->get(1)->addCard($game, Card::heart(13)))
            ->receivedCardFrom($players->get(0), $players->get(0)->addCard($game, Card::heart(2)))
            ->selectHandWinner();

        (new SelectStartingTurn())->handle(new HandWasRequested($game));

        $this->assertEquals(2, $game->fresh()->next_turn);
        $this->assertEquals(2, $game->fresh()->start_turn);
    }
}
