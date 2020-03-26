<?php

namespace Tests\Unit\Rules;

use App\Game;
use App\Player;
use Tests\TestCase;
use App\Rules\TimeToPlayCard;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TimeToPlayCardTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function returns_false_when_it_is_not_time_to_play_card()
    {
        $game = Game::setup();

        $this->assertFalse((new TimeToPlayCard($game))->passes('bid', 7));
    }

    /** @test */
    function returns_true_when_it_is_time_to_play_card()
    {
        $game = Game::setup([factory(Player::class, 2)->create()])
            ->start()
            ->startRound()
            ->startBidding()
            ->compete();

        $this->assertTrue((new TimeToPlayCard($game))->passes('bid', 7));
    }
}
