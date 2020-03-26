<?php

namespace Tests\Feature;

use App\Game;
use App\Player;
use App\Rules\FreeAgent;
use App\Rules\TeamExists;
use Tests\Feature\TestCase;
use App\Rules\EmptyPosition;
use Illuminate\Foundation\Testing\RefreshDatabase;

class JoinGameTest extends TestCase
{
    use RefreshDatabase;

    protected function validParams($overrides = [])
    {
        return array_merge([
            'team' => Game::HOME_TEAM,
        ], $overrides);
    }

    /** @test */
    function join_game()
    {
        $user = $this->loginWithPermission();
        $game = factory(Game::class)->create();

        $response = $this->json('POST', "api/game/{$game->id}/player", [
            'team' => Game::HOME_TEAM,
        ]);

        $response->assertStatus(200);
        tap($game->fresh(), function ($game) use ($user) {
            $this->assertEquals(1, $game->players()->count());
            $this->assertEquals(1, $game->players()->first()->pivot->team);
            $this->assertEquals(1, $game->players()->first()->pivot->position);
            $this->assertEquals($user->player->id, $game->players()->first()->id);
            $this->assertEquals($user->player->name, $game->players()->first()->name);
        });
    }

    /** @test */
    function cannot_join_a_game_multiple_times()
    {
        $user = $this->loginWithPermission();
        $game = factory(Game::class)->create();
        $user->player->join($game, Game::HOME_TEAM);

        $response = $this->json('POST', "api/game/{$game->id}/player", $this->validParams([
            'team' => Game::HOME_TEAM,
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['team' => (new FreeAgent($game, $user->player))->message()]);
        $this->assertCount(1, $game->players);
    }

    /** @test */
    function cannot_join_a_full_team()
    {
        $user = $this->loginWithPermission();
        $game = factory(Game::class)->create();
        factory(Player::class)->create()->join($game, Game::HOME_TEAM);
        factory(Player::class)->create()->join($game, Game::HOME_TEAM);

        $response = $this->json('POST', "api/game/{$game->id}/player", $this->validParams([
            'team' => Game::HOME_TEAM,
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['team' => (new EmptyPosition($game))->message()]);
        $this->assertCount(2, $game->players);
        $this->assertFalse($user->player->joined($game));
    }

    /** @test */
    function team_is_required()
    {
        $user = $this->loginWithPermission();
        $game = factory(Game::class)->create();

        $response = $this->json('POST', "api/game/{$game->id}/player", $this->validParams([
            'team' => null,
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['team']);
        $this->assertFalse($user->player->joined($game));
    }

    /** @test */
    function team_must_exist()
    {
        $user = $this->loginWithPermission();
        $game = factory(Game::class)->create();

        $response = $this->json('POST', "api/game/{$game->id}/player", $this->validParams([
            'team' => 1000,
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['team' => (new TeamExists($game))->message()]);
        $this->assertFalse($user->player->joined($game));
    }
}
