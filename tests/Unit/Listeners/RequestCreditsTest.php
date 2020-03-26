<?php

namespace Tests\Unit\Listeners;

use App\Game;
use App\Player;
use Tests\TestCase;
use App\Listeners\RequestCredits;
use App\Events\CreditsWereRequested;
use App\Events\GameWinnerWasSelected;
use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RequestsCreditsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function dispatches_credits_were_requested()
    {
        Event::fake(CreditsWereRequested::class);
        $game = Game::setup($players = factory(Player::class, 4)->create());

        (new RequestCredits())->handle(new GameWinnerWasSelected($game));

        Event::assertDispatched(CreditsWereRequested::class);
    }
}
