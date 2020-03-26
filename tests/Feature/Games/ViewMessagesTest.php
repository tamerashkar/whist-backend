<?php

namespace Tests\Feature;

use App\Game;
use App\Player;
use App\Message;
use Tests\Feature\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ViewMessagesTest extends TestCase
{
    use RefreshDatabase;

    protected function validParams($overrides = [])
    {
        return array_merge([], $overrides);
    }

    /** @test */
    function view_messages_for_game()
    {
        $this->loginWithPermission();
        $game = Game::setup($players = factory(Player::class, 2)->create());
        $messages = factory(Message::class, 4)->create(['game_id' => $game->id]);

        $response = $this->json('GET', "api/game/{$game->id}/message");

        $response->assertStatus(200);
        tap($response->decodeResponseJson(), function ($response) {
            $this->assertCount(4, $response['data']);
        });
    }
}
