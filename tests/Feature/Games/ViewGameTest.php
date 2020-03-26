<?php

namespace Tests\Feature;

use App\Game;
use App\Player;
use Tests\Feature\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ViewGameTest extends TestCase
{
    use RefreshDatabase;

    protected function validParams($overrides = [])
    {
        return array_merge([], $overrides);
    }

    /** @test */
    function view_game()
    {
        $this->loginWithPermission();
        $game = factory(Game::class)->create();
        $player1 = factory(Player::class)->create();
        $player2 = factory(Player::class)->create();
        $player1->join($game, 1);
        $player2->join($game, 2);

        $response = $this->json('GET', "api/game/{$game->id}");

        $response->assertStatus(200);
        tap($response->decodeResponseJson(), function ($response) use ($game, $player1, $player2) {
            $this->assertEquals($game->id, $response['data']['id']);
            $this->assertSame('team_selection', $response['data']['status']);
            $this->assertSame(1, $response['data']['turn']);
            $this->assertCount(2, $response['data']['players']);
            $this->assertEquals(1, $response['data']['players'][0]['team']);
            $this->assertEquals(2, $response['data']['players'][1]['team']);
        });
    }
}
