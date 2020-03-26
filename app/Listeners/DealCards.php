<?php

namespace App\Listeners;

use App\Deck;
use App\Events\CardsWereDealt;

class DealCards
{
    protected $deck;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(Deck $deck)
    {
        $this->deck = $deck;
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        $event->game->deal($this->deck);

        event(new CardsWereDealt($event->game));
    }
}
