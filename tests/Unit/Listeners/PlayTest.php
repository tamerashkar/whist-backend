<?php

namespace Tests\Unit\Listeners;

use App\Deck;
use App\Game;
use App\Player;
use Tests\TestCase;
use App\Listeners\Play;
use App\Events\CardWasRequested;
use App\Events\PlayerHasPlayedCard;
use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PlayTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function plays_card_when_player_is_a_robot()
    {
        Event::fake([PlayerHasPlayedCard::class]);
        $game = Game::setup($players = factory(Player::class, 2)->states('robot')->create())
            ->start($players->get(0))
            ->deal(Deck::create());

        (new Play())->handle(new CardWasRequested($game));

        $this->assertNotNull($game->activePlayer()->pivot->suit);
        $this->assertNotNull($game->activePlayer()->pivot->value);
        Event::assertDispatched(PlayerHasPlayedCard::class, function ($event) use ($game) {
            return $event->game === $game;
        });
    }

    /** @test */
    function does_not_play_card_when_player_is_not_robot()
    {
        Event::fake([PlayerHasPlayedCard::class]);
        $game = Game::setup($players = factory(Player::class, 2)->create())
            ->start($players->get(0))
            ->deal(Deck::create());

        (new Play())->handle(new CardWasRequested($game));

        $this->assertNull($game->activePlayer()->pivot->suit);
        $this->assertNull($game->activePlayer()->pivot->value);
        Event::assertNotDispatched(PlayerHasPlayedCard::class);
    }
}
