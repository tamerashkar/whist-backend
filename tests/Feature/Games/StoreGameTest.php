<?php

namespace Tests\Feature;

use App\Game;
use App\User;
use Tests\Feature\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StoreGameTest extends TestCase
{
    use RefreshDatabase;

    protected function validParams($overrides = [])
    {
        return array_merge([], $overrides);
    }

    protected function loginWithPermission()
    {
        $user = factory(User::class)->create();
        $this->apiLoginUsing($user);
        return $user;
    }

    /** @test */
    function creates_a_game()
    {
        $this->loginWithPermission();

        $response = $this->json('POST', 'api/game', []);

        $response->assertStatus(201);
        $this->assertSame(1, Game::count());
    }
}
