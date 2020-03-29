<?php

namespace Tests\Unit;

use App\Card;
use App\Deck;
use App\Game;
use App\Player;
use App\GameStatus;
use Tests\TestCase;
use InvalidArgumentException;
use App\Exceptions\DealerHasNotBeenSet;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GameTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function setup_a_game()
    {
        $game = Game::setup(factory(Player::class, 2)->create());
        $this->assertNotNull(Game::find($game->id));
        $this->assertSame(GameStatus::TEAM_SELECTION, $game->status);
        $this->assertSame(Game::defaultPointsToWin(), $game->pointsToWin());
        $this->assertSame(1, $game->next_turn);
        $this->assertSame(1, $game->start_turn);
        $this->assertCount(2, $game->players);
        $this->assertSame(1, (int) $game->players->first()->pivot->team);
        $this->assertSame(1, (int) $game->players->first()->pivot->position);
    }

    /** @test */
    function sets_default_points_to_win()
    {
        Game::defaultPointsToWin(13);
        $this->assertSame(13, Game::defaultPointsToWin());
        Game::defaultPointsToWin(52);
    }

    /** @test */
    function sets_points_to_win()
    {
        $game = Game::setup($players = factory(Player::class, 2)->create());
        $this->assertSame(Game::defaultPointsToWin(), $game->pointsToWin());
        $this->assertSame(1, $game->pointsToWin(1)->pointsToWin());
    }

    /** @test */
    function scopes_players_for_team()
    {
        $game = Game::setup($players = factory(Player::class, 2)->create());
        $this->assertEquals([$players->first()->id], $game->playersForTeam(1)->get()->pluck('id')->toArray());
        $this->assertEquals([$players->last()->id], $game->playersForTeam(2)->get()->pluck('id')->toArray());
    }

    /** @test */
    function sets_the_dealer()
    {
        $dealer = factory(Player::class)->create();
        $player2 = factory(Player::class)->create();
        $game = Game::setup([$dealer, $player2]);

        $game->setDealer($dealer);

        $this->assertSame($dealer->game($game)->pivot->position, $game->dealer_position);
        $this->assertSame($dealer->id, $game->dealer()->id);
    }

    /** @test */
    function throws_exception_when_dealer_has_not_been_set()
    {
        $this->expectException(DealerHasNotBeenSet::class);

        $dealer = factory(Player::class)->create();
        $player2 = factory(Player::class)->create();
        $game = Game::setup([$dealer, $player2]);

        $game->dealer();
    }

    /** @test */
    function returns_players_ordered_from_left_of_the_dealer()
    {
        $game = Game::setup($players = factory(Player::class, 4)->create())->setDealer($players->first());

        $this->assertEquals(
            [$players->get(1)->id, $players->get(2)->id, $players->get(3)->id, $players->get(0)->id],
            $game->setDealer($players->get(0))->playersOrderedFromLeftOfDealer()->pluck('id')->toArray()
        );

        $this->assertEquals(
            [$players->get(2)->id, $players->get(3)->id, $players->get(0)->id, $players->get(1)->id],
            $game->setDealer($players->get(1))->playersOrderedFromLeftOfDealer()->pluck('id')->toArray()
        );

        $this->assertEquals(
            [$players->get(3)->id, $players->get(0)->id, $players->get(1)->id, $players->get(2)->id],
            $game->setDealer($players->get(2))->playersOrderedFromLeftOfDealer()->pluck('id')->toArray()
        );

        $this->assertEquals(
            [$players->get(0)->id, $players->get(1)->id, $players->get(2)->id, $players->get(3)->id],
            $game->setDealer($players->get(3))->playersOrderedFromLeftOfDealer()->pluck('id')->toArray()
        );
    }

    /** @test */
    function return_whether_game_has_a_dealer()
    {
        $game = Game::setup($players = factory(Player::class, 2)->create());
        $this->assertFalse($game->hasDealer());
        $this->assertTrue($game->setDealer($players->first())->hasDealer());
    }

    /** @test */
    function starts_a_game()
    {
        $this->assertEquals(GameStatus::START, Game::setup(factory(Player::class, 2)->create())->start()->status);
    }

    /** @test */
    function starts_a_game_with_dealer()
    {
        $game = Game::setup($players = factory(Player::class, 2)->create())->start($players->get(1));

        $this->assertEquals(GameStatus::START, $game->status);
        $this->assertSame($players->get(1)->id, $game->dealer()->id);
    }

    /** @test */
    function returns_active_player()
    {
        $game = Game::setup($players = factory(Player::class, 2)->create())
            ->start($players->get(0))
            ->nextTurn();

        $this->assertSame($players->get(1)->id, $game->activePlayer()->id);
    }

    /** @test */
    function starts_round()
    {
        $this->assertEquals(GameStatus::ROUND, Game::setup(factory(Player::class, 2)->create())->startRound()->status);
    }

    /** @test */
    function starts_round_and_clears_previous_round()
    {
        $game = Game::setup($players = factory(Player::class, 2)->create())
            ->start($players->get(0))
            ->receivedBidFrom($players->get(1), 7)
            ->receivedBidFrom($players->get(0), 7)
            ->selectBidWinner()
            ->setStartTurn(2)
            ->setNextTurn(2)
            ->compete();

        $game->receivedCardFrom($players->get(0), $players->get(0)->addCard($game, Card::heart(2)))
            ->receivedCardFrom($players->get(1), $players->get(1)->addCard($game, Card::heart(4)))
            ->selectTrumpSuit()
            ->selectHandWinner();

        $game->startRound();

        foreach ($game->players()->get() as $player) {
            $this->assertNull($game->trumpSuit());
            $this->assertNull($player->pivot->bid);
            $this->assertFalse((bool) $player->pivot->bid_winner);
            $this->assertNull($player->pivot->suit);
            $this->assertNull($player->pivot->value);
            $this->assertFalse((bool) $player->pivot->hand_winner);
            $this->assertEquals(0, $player->pivot->hand_wins);
        }
    }

    /** @test */
    function deals_cards_for_two_players()
    {
        $game = Game::setup($players = factory(Player::class, 2)->create())->start($players->first());

        $game->deal(Deck::create());

        $this->assertHasCards(Deck::create(), $players->get(1)->cardsForGame($game)->get(), 0, 2);
        $this->assertHasCards(Deck::create(), $players->get(0)->cardsForGame($game)->get(), 1, 2);
    }

    /** @test */
    function deals_cards_for_four_players()
    {
        $game = Game::setup($players = factory(Player::class, 4)->create())->start($players->first());

        $game->deal(Deck::create());

        $this->assertHasCards(Deck::create(), $players->get(1)->cardsForGame($game)->get(), 0, 4);
        $this->assertHasCards(Deck::create(), $players->get(2)->cardsForGame($game)->get(), 1, 4);
        $this->assertHasCards(Deck::create(), $players->get(3)->cardsForGame($game)->get(), 2, 4);
        $this->assertHasCards(Deck::create(), $players->get(0)->cardsForGame($game)->get(), 3, 4);
    }

    protected function assertHasCards(Deck $deck, $cards, $position = 0, $total = 4)
    {
        $this->assertCount(13, $cards);
        foreach ($cards as $key => $card) {
            $this->assertSame($card->suit, $deck->card($key * $total + $position)->suit());
            $this->assertSame($card->value, $deck->card($key * $total + $position)->value());
        }
    }

    /** @test */
    function sets_start_turn()
    {
        $game = Game::setup($players = factory(Player::class, 4)->create())->start($players->first());

        $this->assertEquals(1, $game->start_turn);
        $this->assertEquals(2, $game->setStartTurn(2)->start_turn);
    }

    /** @test */
    function sets_next_turn()
    {
        $game = Game::setup($players = factory(Player::class, 4)->create())->start($players->first());

        $this->assertEquals(1, $game->next_turn);
        $this->assertEquals(2, $game->setNextTurn(2)->next_turn);
    }

    /** @test */
    function moves_to_next_turn()
    {
        $game = Game::setup($players = factory(Player::class, 4)->create())->start($players->first());

        $this->assertEquals(1, $game->next_turn);
        $this->assertEquals(2, $game->nextTurn()->next_turn);
        $this->assertEquals(3, $game->nextTurn()->next_turn);
        $this->assertEquals(4, $game->nextTurn()->next_turn);
        $this->assertEquals(1, $game->nextTurn()->next_turn);
    }

    /** @test */
    function moves_to_start_turn_from_another_turn()
    {
        $game = Game::setup($players = factory(Player::class, 4)->create())->start($players->first());

        $this->assertEquals(1, $game->start_turn);
        $this->assertEquals(2, $game->startTurnFrom(1)->start_turn);
        $this->assertEquals(2, $game->startTurnFrom(1)->start_turn);
        $this->assertEquals(3, $game->startTurnFrom(2)->start_turn);
        $this->assertEquals(4, $game->startTurnFrom(3)->start_turn);
        $this->assertEquals(1, $game->startTurnFrom(4)->start_turn);
    }

    /** @test */
    function moves_to_next_turn_from_another_turn()
    {
        $game = Game::setup($players = factory(Player::class, 4)->create())->start($players->first());

        $this->assertEquals(1, $game->next_turn);
        $this->assertEquals(2, $game->nextTurnFrom(1)->next_turn);
        $this->assertEquals(2, $game->nextTurnFrom(1)->next_turn);
        $this->assertEquals(3, $game->nextTurnFrom(2)->next_turn);
        $this->assertEquals(4, $game->nextTurnFrom(3)->next_turn);
        $this->assertEquals(1, $game->nextTurnFrom(4)->next_turn);
    }

    /** @test */
    function sets_bid_for_player()
    {
        $game = Game::setup($players = factory(Player::class, 2)->create())->start($players->first());

        $game->receivedBidFrom($players->first(), 7);

        tap($game->players()->where('player_id', $players->first()->id)->first(), function ($player) {
            $this->assertEquals(7, $player->pivot->bid);
        });
    }

    /** @test */
    function returns_true_when_all_bids_have_been_received_for_round()
    {
        $game = Game::setup($players = factory(Player::class, 2)->create())->start($players->first());

        $game->receivedBidFrom($players->get(0), 7);
        $game->receivedBidFrom($players->get(1), 7);

        $this->assertTrue($game->hasReceivedAllBids());
    }

    /** @test */
    function returns_false_when_a_bid_is_missing_for_round()
    {
        $game = Game::setup($players = factory(Player::class, 2)->create())->start($players->first());

        $game->receivedBidFrom($players->get(0), 7);

        $this->assertFalse($game->hasReceivedAllBids());
    }

    /** @test */
    function returns_the_player_with_winning_bid()
    {
        $players = factory(Player::class, 4)->create();
        $game = Game::setup($players)
            ->start($players->get(3))
            ->receivedBidFrom($players->get(0), 7)
            ->receivedBidFrom($players->get(1), 8)
            ->receivedBidFrom($players->get(2), 0)
            ->receivedBidFrom($players->get(3), 0);

        $this->assertSame($players->get(1)->id, $game->playerWithWinningBid()->id);
    }

    /** @test */
    function returns_the_player_with_winning_bid_dealers_can_match_winning_bid()
    {
        $players = factory(Player::class, 4)->create();
        $game = Game::setup($players)
            ->start($players->get(3))
            ->receivedBidFrom($players->get(0), 7)
            ->receivedBidFrom($players->get(1), 8)
            ->receivedBidFrom($players->get(2), 0)
            ->receivedBidFrom($players->get(3), 8);

        $this->assertSame($players->get(3)->id, $game->playerWithWinningBid()->id);
    }

    /** @test */
    function returns_true_when_there_is_a_bid_winner()
    {
        $players = factory(Player::class, 4)->create();
        $game = Game::setup($players)
            ->start($players->get(3))
            ->receivedBidFrom($players->get(0), 7)
            ->receivedBidFrom($players->get(1), 8)
            ->receivedBidFrom($players->get(2), 0)
            ->receivedBidFrom($players->get(3), 0);

        $this->assertTrue($game->hasBidWinner());
    }

    /** @test */
    function returns_false_when_there_is_a_bid_winner()
    {
        $players = factory(Player::class, 4)->create();
        $game = Game::setup($players)
            ->start($players->get(3))
            ->receivedBidFrom($players->get(0), 0)
            ->receivedBidFrom($players->get(1), 0)
            ->receivedBidFrom($players->get(2), 0)
            ->receivedBidFrom($players->get(3), 0);

        $this->assertFalse($game->hasBidWinner());
    }

    /** @test */
    function selects_bid_winner()
    {
        $players = factory(Player::class, 4)->create();
        $game = Game::setup($players)
            ->start($players->get(3))
            ->receivedBidFrom($players->get(0), 7)
            ->receivedBidFrom($players->get(1), 8)
            ->receivedBidFrom($players->get(2), 0)
            ->receivedBidFrom($players->get(3), 0);

        $game->selectBidWinner();

        $this->assertSame($players->get(1)->id, $game->bidWinner()->id);
        $this->assertTrue((bool) $game->player($players->get(1))->first()->pivot->bid_winner);
        $this->assertFalse((bool) $game->player($players->get(0))->first()->pivot->bid_winner);
        $this->assertFalse((bool) $game->player($players->get(2))->first()->pivot->bid_winner);
        $this->assertFalse((bool) $game->player($players->get(3))->first()->pivot->bid_winner);
    }

    /** @test */
    function starts_competition()
    {
        $this->assertEquals(GameStatus::COMPETE, Game::setup(factory(Player::class, 2)->create())->compete()->status);
    }

    /** @test */
    function starts_competition_and_clear_previous_hand()
    {
        $game = Game::setup($players = factory(Player::class, 2)->create())
            ->start($players->get(0))
            ->receivedBidFrom($players->get(1), 7)
            ->receivedBidFrom($players->get(0), 7)
            ->selectBidWinner()
            ->setStartTurn(2)
            ->setNextTurn(2)
            ->compete();

        $game->receivedCardFrom($players->get(0), $players->get(0)->addCard($game, Card::heart(2)))
            ->receivedCardFrom($players->get(1), $players->get(1)->addCard($game, Card::heart(4)))
            ->selectHandWinner();

        $game->compete();

        foreach ($game->players()->get() as $player) {
            $this->assertNull($player->pivot->suit);
            $this->assertNull($player->pivot->value);
            $this->assertFalse((bool) $player->pivot->hand_winner);
        }
    }

    /** @test */
    function returns_true_when_all_cards_have_been_received_for_the_hand()
    {
        $game = Game::setup($players = factory(Player::class, 2)->create())->start($players->first());

        $game->receivedCardFrom(
            $players->get(0),
            $players->get(0)->cards()->create(['game_id' => $game->id, 'suit' => 'Heart', 'value' => 2])
        );
        $game->receivedCardFrom(
            $players->get(1),
            $players->get(1)->cards()->create(['game_id' => $game->id, 'suit' => 'Heart', 'value' => 2])
        );

        $this->assertTrue($game->hasReceivedAllCards());
    }

    /** @test */
    function returns_false_when_a_card_is_missing_for_the_hand()
    {
        $game = Game::setup($players = factory(Player::class, 2)->create())->start($players->first());

        $game->receivedCardFrom(
            $players->get(0),
            $players->get(0)->cards()->create(['game_id' => $game->id, 'suit' => 'Heart', 'value' => 2])
        );

        $this->assertFalse($game->hasReceivedAllCards());
    }

    /** @test */
    function plays_card_for_a_player()
    {
        $game = Game::setup($players = factory(Player::class, 2)->create())
            ->start($players->first())
            ->nextTurn()
            ->deal(Deck::create());
        $player = $players->get(1);
        $card = $player->cards()->first();
        $this->assertTrue($player->hasCard($game, new Card($card->suit, $card->value)));

        $game->receivedCardFrom($player, $card);

        tap($game->players()->where('player_id', $player->id)->first(), function ($player) use ($game, $card) {
            $this->assertEquals($card->suit, $player->pivot->suit);
            $this->assertEquals($card->value, $player->pivot->value);
            $this->assertFalse($player->hasCard($game, new Card($card->suit, $card->value)));
        });
    }

    /** @test */
    function returns_the_suit_of_the_hand()
    {
        $players = factory(Player::class, 4)->create();
        $game = Game::setup($players)->start($players->get(0))->setStartTurn(2)->setNextTurn(2);

        // Player 1 is the dealer and player 2 is expected to throw the first card.
        // Instead the 4th player does so first, then player 2, player 3 and player 1.
        // Regardless of which card is thrown first, the suit is based on the player
        // that is expected to play the first card, which is player 2.
        $game->receivedCardFrom($players->get(3), $players->get(3)->addCard($game, Card::club(5)))
            ->receivedCardFrom($players->get(1), $players->get(1)->addCard($game, Card::diamond(2)))
            ->receivedCardFrom($players->get(2), $players->get(2)->addCard($game, Card::heart(12)))
            ->receivedCardFrom($players->get(0), $players->get(0)->addCard($game, Card::spade(13)));

        $this->assertEquals(Card::DIAMOND, $game->suit());
    }

    /** @test */
    function sets_trump_suit()
    {
        $game = Game::setup($players = factory(Player::class, 2)->create())->start($players->get(0));
        $this->assertNull($game->trumpSuit());
        $this->assertSame(Card::SPADE, $game->setTrumpSuit(Card::SPADE)->trumpSuit());
    }

    /** @test */
    function throws_exception_for_invalid_trump_suit()
    {
        $this->expectException(InvalidArgumentException::class);

        $game = Game::setup($players = factory(Player::class, 2)->create())->start($players->get(0));

        $game->setTrumpSuit('Clover')->trumpSuit();
    }

    /** @test */
    function returns_first_card_of_hand()
    {
        $players = $this->createPlayers(4);
        $game = $this->startGame($players);
        $this->makeBids($game, $players, [0, 0, 0, 7]);
        $game->setStartTurn(1);
        $game->setNextTurn(2);
        // Players played cards in the wrong order, but we will return the correct first card.
        $game->receivedCardFrom($players->get(3), $players->get(3)->addCard($game, Card::heart(2)));
        $game->receivedCardFrom($players->get(1), $players->get(3)->addCard($game, Card::club(4)));
        $game->receivedCardFrom($players->get(0), $players->get(3)->addCard($game, Card::spade(3)));
        $game->receivedCardFrom($players->get(2), $players->get(3)->addCard($game, Card::diamond(5)));

        $this->assertSame(3, $game->firstCardOfHand()->value());
        $this->assertSame(Card::SPADE, $game->firstCardOfHand()->suit());
    }

    /** @test */
    function selects_trump_suit()
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
        $game->selectTrumpSuit();

        $this->assertSame(Card::SPADE, $game->trumpSuit());
    }

    /** @test */
    function does_not_reselect_trump_suit()
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
        $game->selectTrumpSuit();
        $game->receivedCardFrom($players->get(3), $players->get(3)->addCard($game, Card::heart(12)));
        $game->receivedCardFrom($players->get(0), $players->get(3)->addCard($game, Card::heart(12)));
        $game->receivedCardFrom($players->get(1), $players->get(3)->addCard($game, Card::heart(12)));
        $game->receivedCardFrom($players->get(2), $players->get(3)->addCard($game, Card::heart(12)));
        $game->selectTrumpSuit();

        $this->assertSame(Card::SPADE, $game->trumpSuit());
    }

    /** @test */
    function returns_the_player_with_highest_valued_card_for_suit()
    {
        $players = factory(Player::class, 4)->create();
        $game = Game::setup($players)->start($players->get(0));
        $game->receivedCardFrom($players->get(0), $players->get(0)->addCard($game, Card::heart(2)))
            ->receivedCardFrom($players->get(1), $players->get(1)->addCard($game, Card::heart(12)))
            ->receivedCardFrom($players->get(2), $players->get(2)->addCard($game, Card::spade(3)))
            ->receivedCardFrom($players->get(3), $players->get(3)->addCard($game, Card::spade(13)));

        $this->assertSame($players->get(1)->id, $game->playerWithHighestCardValueFor(Card::HEART)->id);
        $this->assertSame($players->get(3)->id, $game->playerWithHighestCardValueFor(Card::SPADE)->id);
    }

    /** @test */
    function returns_the_player_with_winning_hand()
    {
        $players = factory(Player::class, 4)->create();
        $game = Game::setup($players)->start($players->get(0));
        $game->receivedCardFrom($players->get(0), $players->get(0)->addCard($game, Card::heart(2)))
            ->receivedCardFrom($players->get(1), $players->get(1)->addCard($game, Card::heart(12)))
            ->receivedCardFrom($players->get(2), $players->get(2)->addCard($game, Card::spade(13)))
            ->receivedCardFrom($players->get(3), $players->get(3)->addCard($game, Card::heart(5)));

        $this->assertSame($players->get(1)->id, $game->playerWithWinningHand()->id);
    }

    /** @test */
    function returns_the_player_with_winning_hand_when_trump_suit_is_played()
    {
        $players = factory(Player::class, 4)->create();
        $game = Game::setup($players)->start($players->get(0));
        $game->setTrumpSuit(Card::SPADE)
            ->receivedCardFrom($players->get(0), $players->get(0)->addCard($game, Card::heart(3)))
            ->receivedCardFrom($players->get(1), $players->get(1)->addCard($game, Card::heart(12)))
            ->receivedCardFrom($players->get(2), $players->get(2)->addCard($game, Card::spade(2)))
            ->receivedCardFrom($players->get(3), $players->get(3)->addCard($game, Card::heart(5)));

        $this->assertSame($players->get(2)->id, $game->playerWithWinningHand()->id);
    }

    /** @test */
    function selects_hand_winner()
    {
        $players = factory(Player::class, 4)->create();
        $game = Game::setup($players)->start($players->get(0));
        $game->receivedCardFrom($players->get(0), $players->get(0)->addCard($game, Card::heart(2)))
            ->receivedCardFrom($players->get(1), $players->get(1)->addCard($game, Card::heart(12)))
            ->receivedCardFrom($players->get(2), $players->get(2)->addCard($game, Card::spade(13)))
            ->receivedCardFrom($players->get(3), $players->get(3)->addCard($game, Card::heart(5)));

        $game->selectHandWinner();

        $this->assertSame($players->get(1)->id, $game->handWinner()->id);
        $this->assertEquals(1, $game->player($players->get(1))->first()->pivot->hand_wins);
        $this->assertTrue((bool) $game->player($players->get(1))->first()->pivot->hand_winner);
        $this->assertFalse((bool) $game->player($players->get(0))->first()->pivot->hand_winner);
        $this->assertFalse((bool) $game->player($players->get(2))->first()->pivot->hand_winner);
        $this->assertFalse((bool) $game->player($players->get(3))->first()->pivot->hand_winner);
    }

    /** @test */
    function returns_true_when_all_hands_have_been_received_for_round()
    {
        $game = Game::setup($players = factory(Player::class, 2)->create())->start($players->first());

        $game->receivedCardFrom(
            $players->get(0),
            $players->get(0)->cards()->create(['game_id' => $game->id, 'suit' => 'Heart', 'value' => 2])
        );
        $game->receivedCardFrom(
            $players->get(1),
            $players->get(1)->cards()->create(['game_id' => $game->id, 'suit' => 'Heart', 'value' => 2])
        );

        $this->assertTrue($game->hasReceivedAllHands());
    }

    /** @test */
    function returns_false_when_a_hand_still_remains_in_the_round()
    {
        $game = Game::setup($players = factory(Player::class, 2)->create())->start($players->first());

        $game->receivedCardFrom(
            $players->get(0),
            $players->get(0)->cards()->create(['game_id' => $game->id, 'suit' => 'Heart', 'value' => 2])
        );
        $players->get(0)->addCard($game, Card::heart(4));
        $players->get(1)->addCard($game, Card::heart(5));

        $this->assertFalse($game->hasReceivedAllHands());
    }

    /** @test */
    function returns_points_for_team_when_they_have_won_their_bet()
    {
        $game = Game::setup($players = factory(Player::class, 4)->create())->start($players->get(0));

        $this->makeBids($game, $players, [0, 7, 8, 0])
            ->playHands($game, $players, [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]);

        $this->assertSame(13, $game->homeTeamPointsForRound());
        $this->assertSame(0, $game->guestTeamPointsForRound());
    }

    /** @test */
    function returns_points_for_team_when_they_have_lost_their_bet()
    {
        $game = Game::setup($players = factory(Player::class, 4)->create())->start($players->get(0));

        $this->makeBids($game, $players, [0, 7, 8, 0])
            ->playHands($game, $players, [0, 0, 0, 0, 0, 0, 0, 1, 1, 1, 1, 1, 1]);

        $this->assertSame(-8, $game->homeTeamPointsForRound());
        $this->assertSame(0, $game->guestTeamPointsForRound());
    }

    /** @test */
    function returns_points_for_team_when_other_team_has_lost_bet_and_we_stole_the_win()
    {
        $game = Game::setup($players = factory(Player::class, 4)->create())->start($players->get(0));

        $this->makeBids($game, $players, [0, 7, 8, 0])
            ->playHands($game, $players, [0, 0, 0, 0, 0, 1, 1, 1, 1, 1, 1, 1, 1]);

        $this->assertSame(-8, $game->homeTeamPointsForRound());
        $this->assertSame(8, $game->guestTeamPointsForRound());
    }

    /** @test */
    function selects_round_winner()
    {
        $players = factory(Player::class, 4)->create();
        $game = Game::setup($players)->start($players->get(0));

        $this->makeBids($game, $players, [0, 7, 8, 8])
            ->playHands($game, $players, [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1]);

        $round = $game->selectRoundWinner()->round();

        $this->assertSame($players->get(2)->id, $round->bid_winner);
        $this->assertSame(8, $round->bid);
        $this->assertSame(12, $round->home_team_points);
        $this->assertSame(0, $round->guest_team_points);
        $this->assertSame(12, $round->home_team_points);
        $this->assertSame(0, $round->guest_team_points);
    }

    /** @test */
    function selects_no_round_winner_when_there_is_no_winner()
    {
        $players = factory(Player::class, 4)->create();
        $game = Game::setup($players)->start($players->get(0));
        $this->makeBids($game, $players, [0, 0, 0, 0]);

        $game->selectRoundWinner();

        $this->assertNull($game->round());
    }

    /** @test */
    function returns_true_when_game_has_ended_with_a_winner()
    {
        $players = factory(Player::class, 4)->create();
        $game = Game::setup($players)->start($players->get(0));
        $this->makeBids($game, $players, [0, 7, 8, 8])
            ->playHands($game, $players, [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0])
            ->playHands($game, $players, [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0])
            ->playHands($game, $players, [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0])
            ->playHands($game, $players, [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]);

        $game->pointsToWin(52)->selectRoundWinner();

        $this->assertTrue($game->hasWinner());
    }

    /** @test */
    function returns_false_when_game_has_ended_with_a_winner()
    {
        $players = factory(Player::class, 4)->create();
        $game = Game::setup($players)->start($players->get(0));
        $this->makeBids($game, $players, [0, 7, 8, 8])
            ->playHands($game, $players, [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1]);

        $game->pointsToWin(52)->selectRoundWinner();

        $this->assertFalse($game->hasWinner());
    }

    /** @test */
    function selects_game_winner()
    {
        $this->assertEquals(GameStatus::WINNER, Game::setup(factory(Player::class, 2)->create())->selectGameWinner()->status);
    }

    /** @test */
    function returns_game_winner_team_name()
    {
        $players = factory(Player::class, 4)->create();
        $game = Game::setup($players)->start($players->get(0));
        $game->pointsToWin(12);
        $this->makeBids($game, $players, [0, 7, 8, 8])
            ->playHands($game, $players, [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1]);
        $game->selectRoundWinner();
        $game->selectGameWinner();

        $this->assertSame('Home', $game->winningTeamName());
    }

    /** @test */
    function returns_whether_game_has_team()
    {
        $this->assertTrue(Game::setup()->hasTeam(Game::HOME_TEAM));
        $this->assertFalse(Game::setup()->hasTeam(1000));
    }
}
