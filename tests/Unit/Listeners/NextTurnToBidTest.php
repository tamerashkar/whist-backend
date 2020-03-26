<?php

namespace Tests\Unit\Listeners;

use App\Game;
use App\Player;
use Tests\TestCase;
use App\Events\PlayerHasBid;
use App\Listeners\NextTurnToBid;
use Illuminate\Foundation\Testing\RefreshDatabase;

class NextTurnToBidTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function moves_to_next_turn_from_previous_turn()
    {
        $game = Game::setup($players = factory(Player::class, 2)->create())->start($players->first());
        $game->receivedBidFrom($players->get(0), 7);

        (new NextTurnToBid())->handle(new PlayerHasBid($game, $players->get(0), 7));

        $this->assertEquals(2, $game->fresh()->next_turn);
    }

    /** @test */
    function does_not_move_turns_when_we_have_received_all_bids()
    {
        $game = Game::setup($players = factory(Player::class, 2)->create())->start($players->first());
        $game->receivedBidFrom($players->get(0), 7)
            ->nextTurn()
            ->receivedBidFrom($players->get(1), 7);
        $this->assertEquals(2, $game->fresh()->next_turn);

        (new NextTurnToBid())->handle(new PlayerHasBid($game, $players->get(1), 7));

        $this->assertEquals(2, $game->fresh()->next_turn);
    }
}
