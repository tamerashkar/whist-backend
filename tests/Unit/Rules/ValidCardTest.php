<?php

namespace Tests\Unit\Rules;

use App\Card;
use App\Game;
use App\Player;
use Tests\TestCase;
use App\Rules\ValidCard;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ValidCardTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function card_must_be_valid()
    {
        $players = factory(Player::class, 2)->create();
        $game = Game::setup($players);
        $game->receivedCardFrom($players->get(0), $players->get(0)->addCard($game, Card::heart(10)));
        $rule = new ValidCard($game, $players->get(1));

        $this->assertFalse($rule->passes('card', ['suit' => 'INVALID', 'value' => 2]));
        $this->assertFalse($rule->passes('card', ['suit' => Card::HEART, 'value' => 1]));

        $players->get(1)->addCard($game, Card::heart(2));
        $this->assertTrue($rule->passes('card', ['suit' => Card::HEART, 'value' => 2]));
    }

    /** @test */
    function player_must_have_card()
    {
        $players = factory(Player::class, 2)->create();
        $game = Game::setup($players);
        $game->receivedCardFrom($players->get(0), $players->get(0)->addCard($game, Card::heart(10)));
        $rule = new ValidCard($game, $players->get(1));

        $this->assertFalse($rule->passes('card', ['suit' => Card::HEART, 'value' => 2]));
        $this->assertEquals("You do not have this card", $rule->message());

        $players->get(1)->addCard($game, Card::heart(2));
        $this->assertTrue($rule->passes('card', ['suit' => Card::HEART, 'value' => 2]));
    }

    /** @test */
    function player_cannot_play_different_suit_when_player_has_a_card_of_the_same_suit()
    {
        $players = factory(Player::class, 2)->create();
        $game = Game::setup($players);
        $game->receivedCardFrom($players->get(0), $players->get(0)->addCard($game, Card::spade(10)));
        $players->get(1)->addCard($game, Card::heart(2));
        $players->get(1)->addCard($game, Card::spade(2));
        $rule = new ValidCard($game, $players->get(1));

        $this->assertFalse($rule->passes('card', ['suit' => Card::HEART, 'value' => 2]));
        $this->assertEquals("You must play a {$game->suit()} card", $rule->message());
        $this->assertTrue($rule->passes('card', ['suit' => Card::SPADE, 'value' => 2]));
    }

    /** @test */
    function player_can_play_different_suit_when_player_does_not_have_a_card_of_the_same_suit()
    {
        $players = factory(Player::class, 2)->create();
        $game = Game::setup($players);
        $game->receivedCardFrom($players->get(0), $players->get(0)->addCard($game, Card::spade(10)));
        $players->get(1)->addCard($game, Card::heart(2));
        $rule = new ValidCard($game, $players->get(1));

        $rule->passes('card', ['suit' => Card::HEART, 'value' => 2]);
        $this->assertTrue($rule->passes('card', ['suit' => Card::HEART, 'value' => 2]));
    }
}
