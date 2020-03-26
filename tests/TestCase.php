<?php

namespace Tests;

use App\Card;
use App\Game;
use Throwable;
use App\Player;
use App\Exceptions\Handler;
use Laravel\Passport\Passport;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    public function apiLoginUsing($user)
    {
        Passport::actingAs($user);
        return $this;
    }

    protected function disableExceptionHandling()
    {
        $this->app->instance(ExceptionHandler::class, new class extends Handler
        {
            public function __construct()
            {
            }

            public function report(Throwable $e)
            {
                // no-op
            }

            public function render($request, Throwable $e)
            {
                throw $e;
            }
        });
    }

    protected function createPlayers($amount)
    {
        return factory(Player::class, $amount)->create();
    }

    protected function startGame($players, $dealer = null)
    {
        return Game::setup($players)->start($dealer ?: $players->get(0));
    }

    protected function makeBids($game, $players, $bids = [0, 0, 0, 8])
    {
        foreach ($bids as $key => $bid) {
            $game->receivedBidFrom($players->get($key), $bid);
        }

        $game->selectBidWinner();

        return $this;
    }

    protected function playHands($game, $players, $winners = [])
    {
        foreach ($winners as $winner) {
            $this->playHand($game, $players, $winner);
        }

        return $this;
    }

    protected function playHand($game, $players, $winner = 0)
    {
        foreach ($players as $key => $player) {
            $value = $key + 2 + ($key === $winner ? count($players) : 0);
            $game->receivedCardFrom($player, $players->get($key)->addCard($game, Card::heart($value)));
        }

        $game->selectHandWinner();

        return $this;
    }
}
