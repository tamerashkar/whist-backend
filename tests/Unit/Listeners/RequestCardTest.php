<?php

namespace Tests\Unit\Listeners;

use App\Game;
use App\Player;
use Tests\TestCase;
use App\Events\HandHasEnded;
use App\Listeners\RequestCard;
use App\Events\CardWasRequested;
use App\Events\PlayerHasPlayedCard;
use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RequestCardTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function dispatches_card_was_request_when_last_card_has_not_been_received()
    {
        Event::fake([CardWasRequested::class, HandHasEnded::class]);
        $game = Game::setup($players = factory(Player::class, 2)->create())->start($players->first());
        $game->receivedCardFrom(
            $players->get(0),
            $card = $players->get(0)->cards()->create(['game_id' => $game->id, 'suit' => 'Heart', 'value' => 2])
        );

        (new RequestCard())->handle(new PlayerHasPlayedCard($game, $players->get(0), $card));

        Event::assertDispatched(CardWasRequested::class);
        Event::assertNotDispatched(HandHasEnded::class);
    }

    /** @test */
    function dispatches_hand_has_ended_event_when_last_card_has_been_received()
    {
        Event::fake([CardWasRequested::class, HandHasEnded::class]);
        $game = Game::setup($players = factory(Player::class, 2)->create())->start($players->first());
        $game->receivedCardFrom(
            $players->get(0),
            $players->get(0)->cards()->create(['game_id' => $game->id, 'suit' => 'Heart', 'value' => 2])
        );
        $game->receivedCardFrom(
            $players->get(1),
            $card = $players->get(1)->cards()->create(['game_id' => $game->id, 'suit' => 'Heart', 'value' => 2])
        );

        (new RequestCard())->handle(new PlayerHasPlayedCard($game, $players->get(1), $card));

        Event::assertDispatched(HandHasEnded::class);
        Event::assertNotDispatched(CardWasRequested::class);
    }
}
