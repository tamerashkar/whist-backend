<?php

namespace Tests\Unit\Listeners;

use App\Game;
use Tests\TestCase;
use App\Events\GameEvent;
use App\Events\Announceable;
use App\Events\MessageWasCreated;
use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GameEventTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function records_event()
    {
        new GenericEvent($game = Game::setup());

        $this->assertSame('GenericEvent', $game->event);
        $this->assertSame('GenericEvent', $game->events()->first()->name);
        $this->assertSame($game->toArray(), $game->events()->first()->data);
    }

    /** @test */
    function dispatches_message_was_created_for_an_announceable_game_event()
    {
        Event::fake(MessageWasCreated::class);

        $this->announceableEvent(Game::setup());

        Event::assertDispatched(MessageWasCreated::class);
    }

    /** @test */
    function does_not_dispatch_message_was_created_for_an_non_announceable_game_event()
    {
        Event::fake(MessageWasCreated::class);

        $this->genericEvent(Game::setup());

        Event::assertNotDispatched(MessageWasCreated::class);
    }

    public function genericEvent($params)
    {
        return new class ($params) extends GameEvent
        {
        };
    }

    public function announceableEvent($params)
    {
        return new class ($params) extends GameEvent implements Announceable
        {
            public function message(): array
            {
                return ['body' => 'My announceable event'];
            }
        };
    }
}

class GenericEvent extends GameEvent
{
}
