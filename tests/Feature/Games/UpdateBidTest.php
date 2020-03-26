<?php

namespace Tests\Feature;

use App\Game;
use App\Player;
use App\Rules\Turn;
use App\Rules\ValidBid;
use App\Rules\TimeToBid;
use App\Rules\MissingBid;
use Tests\Feature\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UpdateBidTest extends TestCase
{
    use RefreshDatabase;

    protected function validParams($overrides = [])
    {
        return array_merge([
            'bid' => 0
        ], $overrides);
    }

    /** @test */
    function sets_bid()
    {
        $user = $this->loginWithPermission();
        $game = Game::setup([$dealer = factory(Player::class)->create()]);
        $user->player->join($game, Game::GUEST_TEAM);
        $game->start($dealer)
            ->nextTurn()
            ->startRound()
            ->startBidding();

        $response = $this->json('PUT', "api/game/{$game->id}/player/{$user->player->id}", [
            'bid' => 7,
        ]);

        $response->assertStatus(200);
        tap($response->decodeResponseJson(), function ($response) use ($game, $user) {
            $this->assertEquals(7, $game->player($user->player)->first()->pivot->bid);
        });
    }

    /** @test */
    function cannot_bid_for_another_player()
    {
        $this->loginWithPermission();
        $game = Game::setup($players = factory(Player::class, 2)->create());
        $game->start($players->get(0));

        $response = $this->json('PUT', "api/game/{$game->id}/player/{$players->get(0)->id}", $this->validParams([
            'bid' => 7,
        ]));

        $response->assertStatus(403);
        $this->assertNull($game->player($players->get(0))->first()->pivot->bid);
    }

    /** @test */
    function cannot_bid_twice()
    {
        $user = $this->loginWithPermission();
        $game = Game::setup(factory(Player::class, 1)->create());
        $user->player->join($game, Game::GUEST_TEAM);
        $game->start($user->player);
        $game->receivedBidFrom($user->player, 0);

        $response = $this->json('PUT', "api/game/{$game->id}/player/{$user->player->id}", $this->validParams([
            'bid' => 7,
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['bid' => (new MissingBid($game, $user->player))->message()]);
        tap($response->decodeResponseJson(), function ($response) use ($game, $user) {
            $this->assertEquals(0, $game->player($user->player)->first()->pivot->bid);
        });
    }

    /** @test */
    function cannot_bid_when_it_is_not_bidding_stage()
    {
        $user = $this->loginWithPermission();
        $game = Game::setup([$dealer = factory(Player::class)->create()]);
        $user->player->join($game, Game::GUEST_TEAM);
        $game->start($dealer)->nextTurn();

        $response = $this->json('PUT', "api/game/{$game->id}/player/{$user->player->id}", $this->validParams([
            'bid' => 7,
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['bid' => (new TimeToBid($game, $user->player))->message()]);
        tap($response->decodeResponseJson(), function ($response) use ($game, $user) {
            $this->assertEquals(0, $game->player($user->player)->first()->pivot->bid);
        });
    }

    /** @test */
    function cannot_bid_out_of_turn()
    {
        $user = $this->loginWithPermission();
        $player1 = factory(Player::class)->create();
        $player2 = factory(Player::class)->create();
        $player3 = factory(Player::class)->create();
        $game = Game::setup([$player1, $player2, $player3, $user->player])->start($player1);
        $game->receivedBidFrom($player2, 7);

        $response = $this->json('PUT', "api/game/{$game->id}/player/{$user->player->id}", $this->validParams([
            'bid' => 8,
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['bid' => (new Turn($game, $user->player))->message()]);
        tap($response->decodeResponseJson(), function ($response) use ($game, $user) {
            $this->assertFalse($user->player->hasBid($game));
        });
    }

    /** @test */
    function bid_must_exceed_minimum_bid_for_round()
    {
        $user = $this->loginWithPermission();
        $player1 = factory(Player::class)->create();
        $player2 = factory(Player::class)->create();
        $player3 = factory(Player::class)->create();
        $game = Game::setup([$player1, $player2, $player3, $user->player])->start($player1);
        $game->receivedBidFrom($player2, 7);

        $response = $this->json('PUT', "api/game/{$game->id}/player/{$user->player->id}", $this->validParams([
            'bid' => 7,
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['bid' => (new ValidBid($game, $user->player))->message()]);
        tap($response->decodeResponseJson(), function ($response) use ($game, $user) {
            $this->assertFalse($user->player->hasBid($game));
        });
    }

    /** @test */
    function bid_must_be_same_or_exceed_minimum_bid_for_round_if_player_is_dealer()
    {
        $user = $this->loginWithPermission();
        $player1 = factory(Player::class)->create();
        $player2 = factory(Player::class)->create();
        $player3 = factory(Player::class)->create();
        $game = Game::setup([$player1, $player2, $player3, $user->player])->start($user->player);
        $game->receivedBidFrom($player2, 8);

        $response = $this->json('PUT', "api/game/{$game->id}/player/{$user->player->id}", $this->validParams([
            'bid' => 7,
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['bid' => (new ValidBid($game, $user->player))->message()]);
        tap($response->decodeResponseJson(), function ($response) use ($game, $user) {
            $this->assertFalse($user->player->hasBid($game));
        });
    }

    /** @test */
    function bid_must_be_zero_or_between_seven_and_thirteen()
    {
        $user = $this->loginWithPermission();
        $game = Game::setup(factory(Player::class, 1)->create());
        $user->player->join($game, Game::GUEST_TEAM);
        $game->start($user->player);

        $response = $this->json('PUT', "api/game/{$game->id}/player/{$user->player->id}", $this->validParams([
            'bid' => 14,
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['bid' => (new ValidBid($game, $user->player))->message()]);
        tap($response->decodeResponseJson(), function ($response) use ($game, $user) {
            $this->assertFalse($user->player->hasBid($game));
        });
    }
}
