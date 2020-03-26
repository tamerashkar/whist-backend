<?php

namespace Tests\Unit\Rules;

use App\Card;
use App\Deck;
use App\Game;
use App\Player;
use Tests\TestCase;
use App\Rules\MissingCard;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MissingCardTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function returns_true_when_player_has_not_bid()
    {
        $game = Game::setup();
        $player = factory(Player::class)->create();
        $player->join($game, Game::HOME_TEAM);

        $this->assertTrue((new MissingCard($game, $player))->passes('card', [
            'suit' => Card::HEART,
            'value' => 2
        ]));
    }

    /** @test */
    function returns_false_when_player_has_already_bid()
    {
        $game = Game::setup();
        $players = factory(Player::class, 2)->create();
        $game = Game::setup($players)->start($players->get(0))->deal(Deck::create())->nextTurn();
        $card = $game->player($players->get(0))->first()->cards()->first();
        $game->receivedCardFrom($players->get(0), $card);

        $this->assertFalse((new MissingCard($game, $players->get(0)))->passes('card', [
            'suit' => Card::HEART,
            'value' => 2
        ]));
    }
}
