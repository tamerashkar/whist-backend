<?php

namespace Tests\Unit;

use App\Card;
use App\Game;
use App\Player;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PlayerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function scopes_player_for_game()
    {
        $team = 1;
        $game = factory(Game::class)->create();
        $player = factory(Player::class)->create();
        $this->assertNull($player->game($game));

        $player->join($game, $team);
        $this->assertNotNull($player->game($game));
    }

    /** @test */
    function returns_whether_player_is_robot()
    {
        $this->assertFalse(factory(Player::class)->create()->isRobot());
        $this->assertTrue(factory(Player::class)->states('robot')->create()->isRobot());
    }

    /** @test */
    function joins_a_game()
    {
        $game = factory(Game::class)->create();
        $player = factory(Player::class)->create();

        $player->join($game, Game::HOME_TEAM);

        $this->assertEquals(1, $game->players()->count());
        $this->assertEquals($player->id, $game->players()->first()->id);
        $this->assertEquals(Game::HOME_TEAM, $game->players()->first()->pivot->team);
    }

    /** @test */
    function returns_true_when_player_has_joined_game()
    {
        $game = factory(Game::class)->create();
        $player = factory(Player::class)->create();
        $player->join($game, Game::HOME_TEAM);

        $this->assertTrue($player->joined($game));
    }

    /** @test */
    function returns_false_when_player_has_joined_game()
    {
        $game = factory(Game::class)->create();
        $player = factory(Player::class)->create();

        $this->assertFalse($player->joined($game));
    }

    /** @test */
    function sets_players_position_when_they_join_a_game()
    {
        $team = 1;
        $game = factory(Game::class)->create();
        $player1 = factory(Player::class)->create();
        $player2 = factory(Player::class)->create();

        $player1->join($game, $team);
        $player2->join($game, $team);

        $this->assertEquals(1, $player1->game($game)->pivot->position);
        $this->assertEquals(3, $player2->game($game)->pivot->position);

        $team = 2;
        $game = factory(Game::class)->create();
        $player3 = factory(Player::class)->create();
        $player4 = factory(Player::class)->create();

        $player3->join($game, $team);
        $player4->join($game, $team);

        $this->assertEquals(2, $player3->game($game)->pivot->position);
        $this->assertEquals(4, $player4->game($game)->pivot->position);
    }

    /** @test */
    function returns_cards_for_game()
    {
        $game = Game::setup($players = factory(Player::class, 2)->create())->start($players->first());

        foreach ($players as $key => $player) {
            $player->cards()->create([
                'game_id' => $game->id,
                'suit' => 'Heart',
                'value' => $key + 1,
            ]);
        }

        $player1Cards = $players->get(0)->cardsForGame($game)->get();
        $this->assertCount(1, $player1Cards);
        $this->assertSame(1, $player1Cards->first()->value);
        $this->assertSame('Heart', $player1Cards->first()->suit);
        $player2Cards = $players->get(1)->cardsForGame($game)->get();
        $this->assertCount(1, $player2Cards);
        $this->assertSame(2, $player2Cards->first()->value);
        $this->assertSame('Heart', $player2Cards->first()->suit);
    }

    /** @test */
    function returns_card_for_game()
    {
        $game = Game::setup([$player] = factory(Player::class, 2)->create())->start($player);
        $card = $player->addCard($game, Card::heart(2));

        $this->assertNull($player->cardForGame($game, Card::spade(2))->first());
        $this->assertSame($card->id, $player->cardForGame($game, Card::heart(2))->first()->id);
    }

    /** @test */
    function returns_card_with_suit_for_game()
    {
        $game = Game::setup([$player] = factory(Player::class, 2)->create())->start($player);
        $card = $player->addCard($game, Card::heart(2));

        $this->assertNull($player->cardWithSuitForGame($game, Card::SPADE)->first());
        $this->assertSame($card->id, $player->cardWithSuitForGame($game, Card::HEART)->first()->id);
    }

    /** @test */
    function returns_whether_player_has_card()
    {
        $game = Game::setup([$player] = factory(Player::class, 2)->create())->start($player);
        $card = $player->addCard($game, Card::heart(2));

        $this->assertTrue($player->hasCard($game, Card::heart(2)));
        $this->assertFalse($player->hasCard($game, Card::heart(3)));
    }

    /** @test */
    function adds_cards_to_player()
    {
        $player = factory(Player::class)->create();
        $game = Game::setup([$player])->start($player);

        $player->addCard($game, new Card('Heart', 2));

        $this->assertSame(1, $player->cardsForGame($game)->count());
        $this->assertSame(2, $player->cardsForGame($game)->first()->value);
        $this->assertSame('Heart', $player->cardsForGame($game)->first()->suit);
    }


    /** @test */
    function does_not_add_duplicate_cards()
    {
        $player = factory(Player::class)->create();
        $game = Game::setup([$player])->start($player);

        $player->addCard($game, new Card('Heart', 2));
        $player->addCard($game, new Card('Heart', 2));

        $this->assertSame(1, $player->cardsForGame($game)->count());
    }

    /** @test */
    function returns_true_when_player_has_not_bid()
    {
        $game = Game::setup();
        $player = factory(Player::class)->create();
        $player->join($game, Game::HOME_TEAM);
        $game->receivedBidFrom($player, 0);

        $this->assertTrue($player->hasBid($game));
    }

    /** @test */
    function returns_false_when_player_has_already_bid()
    {
        $game = Game::setup();
        $player = factory(Player::class)->create();
        $player->join($game, Game::HOME_TEAM);

        $this->assertFalse($player->hasBid($game));
    }

    /** @test */
    function returns_true_when_player_has_played_card()
    {
        $game = Game::setup();
        $player = factory(Player::class)->create();
        $player->join($game, Game::HOME_TEAM);
        $game->receivedCardFrom($player, $game->player($player)->first()->addCard($game, Card::heart(2)));

        $this->assertTrue($player->hasPlayedCard($game));
    }

    /** @test */
    function returns_false_when_player_has_not_played_card()
    {
        $game = Game::setup();
        $player = factory(Player::class)->create();
        $player->join($game, Game::HOME_TEAM);

        $this->assertFalse($player->hasPlayedCard($game));
    }
}
