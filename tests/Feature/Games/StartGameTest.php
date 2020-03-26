<?php

namespace Tests\Feature;

use App\Game;
use App\Player;
use App\GameStatus;
use Tests\Feature\TestCase;
use App\Events\GameHasStarted;
use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StartGameTest extends TestCase
{
    use RefreshDatabase;

    protected function validParams($overrides = [])
    {
        return array_merge([], $overrides);
    }

    /** @test */
    function start_game()
    {
        $this->loginWithPermission();
        Event::fake(GameHasStarted::class);
        $game = Game::setup(factory(Player::class, 2)->create());

        $response = $this->json('PUT', "api/game/{$game->id}", [
            'start' => true
        ]);

        $response->assertStatus(200);
        Event::assertDispatched(GameHasStarted::class);
        tap($response->decodeResponseJson(), function ($response) use ($game) {
            $this->assertEquals(GameStatus::find(GameStatus::START)->name, $response['data']['status']);
        });
    }

    /** @test */
    function does_not_start_game_twice()
    {
        $this->loginWithPermission();
        Event::fake(GameHasStarted::class);
        $game = Game::setup(factory(Player::class, 2)->create())->start();

        $response = $this->json('PUT', "api/game/{$game->id}", [
            'start' => true
        ]);

        $response->assertStatus(200);
        Event::assertNotDispatched(GameHasStarted::class);
    }
}
