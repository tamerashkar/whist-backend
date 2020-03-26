<?php

namespace Tests\Feature;

use App\Game;
use Tests\Feature\TestCase;
use App\Events\MessageWasCreated;
use Illuminate\Support\Facades\Event;
use App\Http\Requests\StoreMessageRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StoreMessageTest extends TestCase
{
    use RefreshDatabase;

    protected function validParams($overrides = [])
    {
        return array_merge([], $overrides);
    }

    /** @test */
    function create_message()
    {
        $game = Game::setup();
        $this->loginWithPermission();
        Event::fake(MessageWasCreated::class);

        $response = $this->json('POST', "api/game/{$game->id}/message", [
            'body' => 'My Message'
        ]);

        $response->assertStatus(201);
        Event::assertDispatched(MessageWasCreated::class);
        tap($response->decodeResponseJson(), function ($response) {
            $this->assertSame('My Message', $response['data']['body']);
        });
    }

    /** @test */
    function body_is_required()
    {
        $game = Game::setup();
        $this->loginWithPermission();

        $response = $this->json('POST', "api/game/{$game->id}/message", $this->validParams([
            'body' => null
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['body']);
    }

    /** @test */
    function body_cannot_exceed_max_length()
    {
        $game = Game::setup();
        $this->loginWithPermission();

        $response = $this->json('POST', "api/game/{$game->id}/message", $this->validParams([
            'body' => str_repeat('A', StoreMessageRequest::BODY_MAX_LENGTH + 1)
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['body']);
    }
}
