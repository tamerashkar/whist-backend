<?php

namespace App\Providers;

use App\Listeners\Bid;
use App\Listeners\Play;
use App\Events\HandHasEnded;
use App\Events\PlayerHasBid;
use App\Listeners\DealCards;
use App\Listeners\StartHand;
use App\Events\GameHasEnded;
use App\Listeners\StartRound;
use App\Events\RoundHasEnded;
use App\Listeners\RequestBid;
use App\Events\GameHasStarted;
use App\Events\HandHasStarted;
use App\Events\CardsWereDealt;
use App\Listeners\RequestCard;
use App\Listeners\RequestHand;
use App\Events\BiddingHasEnded;
use App\Events\BidWasRequested;
use App\Events\RoundHasStarted;
use App\Listeners\SelectDealer;
use App\Listeners\StartBidding;
use App\Listeners\RequestRound;
use App\Events\CardWasRequested;
use App\Listeners\NextTurnToBid;
use App\Events\HandWasRequested;
use App\Events\BiddingHasStarted;
use App\Events\DealerWasSelected;
use App\Events\RoundWasRequested;
use App\Listeners\RequestCredits;
use App\Listeners\SelectBidWinner;
use App\Listeners\SelectTrumpSuit;
use App\Events\PlayerHasJoinedTeam;
use App\Events\PlayerHasPlayedCard;
use App\Listeners\SelectHandWinner;
use App\Listeners\SelectGameWinner;
use App\Events\BidWinnerWasSelected;
use App\Events\CreditsWereRequested;
use App\Listeners\SelectRoundWinner;
use App\Events\TrumpSuitWasSelected;
use App\Events\GameWinnerWasSelected;
use App\Events\HandWinnerWasSelected;
use App\Listeners\NextTurnToPlayCard;
use App\Listeners\SelectStartingTurn;
use App\Events\RoundWinnerWasSelected;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],

        PlayerHasJoinedTeam::class => [],

        GameHasStarted::class => [
            RequestRound::class
        ],

        RoundWasRequested::class => [
            StartRound::class,
        ],

        RoundHasStarted::class => [
            SelectDealer::class,
        ],

        DealerWasSelected::class => [
            SelectStartingTurn::class,
            DealCards::class,
        ],

        CardsWereDealt::class => [
            StartBidding::class,
        ],

        BiddingHasStarted::class => [
            RequestBid::class
        ],

        PlayerHasBid::class => [
            NextTurnToBid::class,
            RequestBid::class,
        ],

        BidWasRequested::class => [
            Bid::class,
        ],

        BiddingHasEnded::class => [
            SelectBidWinner::class,
        ],

        BidWinnerWasSelected::class => [
            RequestHand::class
        ],

        HandWasRequested::class => [
            SelectStartingTurn::class,
            StartHand::class,
        ],

        HandHasStarted::class => [
            RequestCard::class
        ],

        PlayerHasPlayedCard::class => [
            SelectTrumpSuit::class,
            NextTurnToPlayCard::class,
            RequestCard::class
        ],

        TrumpSuitWasSelected::class => [],

        CardWasRequested::class => [
            Play::class,
        ],

        HandHasEnded::class => [
            SelectHandWinner::class
        ],

        HandWinnerWasSelected::class => [
            RequestHand::class,
        ],

        RoundHasEnded::class => [
            SelectRoundWinner::class
        ],

        RoundWinnerWasSelected::class => [
            RequestRound::class
        ],

        GameHasEnded::class => [
            SelectGameWinner::class
        ],

        GameWinnerWasSelected::class => [
            RequestCredits::class
        ],

        CreditsWereRequested::class => []
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
