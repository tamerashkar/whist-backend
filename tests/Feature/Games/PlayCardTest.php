<?php

namespace Tests\Feature;

use App\Card;
use App\Deck;
use App\Game;
use App\Player;
use App\Rules\Turn;
use App\Rules\MissingCard;
use Tests\Feature\TestCase;
use App\Rules\TimeToPlayCard;
use App\Events\PlayerHasPlayedCard;
use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PlayCardTest extends TestCase
{
    use RefreshDatabase;

    public function validParams($overrides = [])
    {
        $card = Deck::create()->deal();

        return array_merge([
            'card' => [
                'suit' => $card->suit(),
                'value' => $card->value()
            ]
        ], $overrides);
    }

    /** @test */
    function plays_card()
    {
        Event::fake(PlayerHasPlayedCard::class);
        $user = $this->loginWithPermission();
        $player2 = factory(Player::class)->create();
        $game = Game::setup([$user->player, $player2])->start($player2)->deal(Deck::create())->compete();

        $card = $game->player($user->player)->first()->cards()->first();

        $response = $this->json('PUT', "api/game/{$game->id}/player/{$user->player->id}", [
            'card' => [
                'suit' => $card->suit,
                'value' => $card->value,
            ],
        ]);

        Event::assertDispatched(PlayerHasPlayedCard::class);
        $response->assertStatus(200);
        tap($response->decodeResponseJson(), function ($response) use ($game, $card) {
            $this->assertEquals($card->suit, $response['data']['card']['suit']);
            $this->assertEquals($card->value, $response['data']['card']['value']);
            $this->assertFalse($game->players->get(1)->cards()->where('id', $card->id)->exists());
        });
    }

    /** @test */
    function cannot_play_card_for_another_player()
    {
        $this->loginWithPermission();
        $game = Game::setup($players = factory(Player::class, 2)->create())->start($players->get(0))->nextTurn();

        $game->deal(Deck::create());
        $card = $game->players->get(1)->cards->get(0);

        $response = $this->json('PUT', "api/game/{$game->id}/player/{$players->get(1)->id}", $this->validParams([
            'card' => [
                'suit' => $card->suit,
                'value' => $card->value,
            ],
        ]));

        $response->assertStatus(403);
        $this->assertCount(13, $game->players->get(1)->cards);
    }

    /** @test */
    function cannot_play_card_twice()
    {
        $user = $this->loginWithPermission();
        $player2 = factory(Player::class)->create();
        $game = Game::setup([$user->player, $player2])->start($player2)->deal(Deck::create())->nextTurn()->compete();
        $card = $game->player($user->player)->first()->cards()->first();
        $game->receivedCardFrom($user->player, $card);
        $card = $game->player($user->player)->first()->cards()->first();

        $response = $this->json('PUT', "api/game/{$game->id}/player/{$user->player->id}", $this->validParams([
            'card' => [
                'suit' => $card->suit,
                'value' => $card->value,
            ],
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['card' => (new MissingCard($game, $user->player))->message()]);
        $this->assertCount(12, $game->player($user->player)->first()->cards);
    }

    /** @test */
    function cannot_play_a_card_it_is_not_compete_stage()
    {
        $user = $this->loginWithPermission();
        $player2 = factory(Player::class)->create();
        $game = Game::setup([$user->player, $player2])->start($player2)->deal(Deck::create());
        $card = $game->player($user->player)->first()->cards()->first();

        $response = $this->json('PUT', "api/game/{$game->id}/player/{$user->player->id}", $this->validParams([
            'card' => [
                'suit' => $card->suit,
                'value' => $card->value,
            ],
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['card' => (new TimeToPlayCard($game, $user->player))->message()]);
        $this->assertCount(13, $game->player($user->player)->first()->cards);
    }

    /** @test */
    function cannot_play_card_out_of_turn()
    {
        $user = $this->loginWithPermission();
        $player2 = factory(Player::class)->create();
        $game = Game::setup([$user->player, $player2])->start($user->player)->deal(Deck::create())->nextTurn()->compete();
        $card = $game->player($user->player)->first()->cards()->first();

        $response = $this->json('PUT', "api/game/{$game->id}/player/{$user->player->id}", $this->validParams([
            'card' => [
                'suit' => $card->suit,
                'value' => $card->value,
            ],
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['card' => (new Turn($game, $user->player))->message()]);
        $this->assertCount(13, $game->player($user->player)->first()->cards);
    }

    /** @test */
    function player_must_have_card()
    {
        $user = $this->loginWithPermission();
        $player2 = factory(Player::class)->create();
        $game = Game::setup([$user->player, $player2])->start($player2)->compete();

        $response = $this->json('PUT', "api/game/{$game->id}/player/{$user->player->id}", $this->validParams([
            'card' => [
                'suit' => Card::HEART,
                'value' => 2,
            ],
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['card']);
    }

    /** @test */
    function cannot_play_different_suit_when_player_has_a_card_of_the_same_suit()
    {
        $user = $this->loginWithPermission();
        $player2 = factory(Player::class)->create();
        $game = Game::setup([$user->player, $player2])->start($player2)->setStartTurn(2)->compete();
        $game->receivedCardFrom($player2, $player2->addCard($game, Card::spade(10)));
        $user->player->addCard($game, Card::spade(2));
        $card = $user->player->addCard($game, Card::heart(2));

        $response = $this->json('PUT', "api/game/{$game->id}/player/{$user->player->id}", $this->validParams([
            'card' => [
                'suit' => $card->suit,
                'value' => $card->value,
            ],
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['card']);
    }

    /** @test */
    function can_play_different_suit_when_player_does_not_have_a_card_of_the_same_suit()
    {
        $this->disableExceptionHandling();
        $user = $this->loginWithPermission();
        $player2 = factory(Player::class)->create();
        $game = Game::setup([$user->player, $player2])->start($player2)->compete();
        $player2->addCard($game, Card::spade(11));
        $game->receivedCardFrom($player2, $player2->addCard($game, Card::spade(10)));
        $user->player->addCard($game, Card::heart(2));
        $card = $user->player->addCard($game, Card::heart(3));

        $response = $this->json('PUT', "api/game/{$game->id}/player/{$user->player->id}", $this->validParams([
            'card' => [
                'suit' => $card->suit,
                'value' => $card->value,
            ],
        ]));

        $response->assertStatus(200);
    }
}
