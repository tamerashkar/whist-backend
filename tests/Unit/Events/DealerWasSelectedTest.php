<?php

namespace Tests\Unit\Events;

use App\Game;
use App\Player;
use Tests\TestCase;
use App\Events\DealerWasSelected;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DealerWasSelectedTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function creates_dealer_was_selected_message()
    {
        $game = Game::setup($players = factory(Player::class, 4)->create())
            ->start()
            ->setDealer($players->get(0));

        $message = (new DealerWasSelected($game))->message();

        $this->assertSame("Selected as the dealer", $message['body']);
    }
}
