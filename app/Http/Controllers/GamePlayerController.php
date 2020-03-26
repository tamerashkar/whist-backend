<?php

namespace App\Http\Controllers;

use App\User;
use App\Card;
use App\Game;
use App\Robot;
use App\Player;
use Illuminate\Http\Request;
use App\Events\PlayerHasBid;
use App\Events\PlayerHasJoinedTeam;
use App\Events\PlayerHasLeftTeam;
use App\Events\PlayerHasPlayedCard;
use App\Http\Requests\JoinGameRequest;
use App\Http\Resources\PlayerResource;
use App\Http\Requests\UpdateGamePlayerRequest;

class GamePlayerController extends Controller
{
    public function store(JoinGameRequest $request, Game $game)
    {
        if ($request->robot) {
            $player = $this->createRobot()->join($game, $request->team);
        } else {
            $player = $request->user()->player->join($game, $request->team);
        }

        event(new PlayerHasJoinedTeam($game, $player, $request->team));

        return $this->show($game, $player);
    }

    public function createRobot()
    {
        return Player::create([
            'robot' => true,
            'name' => Robot::randomName(),
            'user_id' => User::first()->id
        ]);
    }

    public function show(Game $game, Player $player)
    {
        return new PlayerResource(
            $game->players()->where('player_id', $player->id)->first()
        );
    }

    public function update(UpdateGamePlayerRequest $request, Game $game, Player $player)
    {
        if ($request->has('bid')) {
            $this->bid($request, $game, $player);
        } else if ($request->has('card')) {
            $this->play($request, $game, $player);
        }
        return $this->show($game, $player);
    }

    public function destroy(Request $request, Game $game, Player $player)
    {
        if ($player = $game->player($player)->first()) {
            $team = $player->pivot->team;

            $player->leave($game);

            event(new PlayerHasLeftTeam($game, $player, $team));
        }

        return true;
    }

    protected function bid(UpdateGamePlayerRequest $request, Game $game, Player $player)
    {
        $game->receivedBidFrom($player, $request->bid);

        event(new PlayerHasBid($game, $player, $request->bid));
    }

    protected function play(Request $request, Game $game, Player $player)
    {
        $card = $player->cardForGame(
            $game,
            new Card($request->card['suit'], $request->card['value'])
        )->first();

        $game->receivedCardFrom($player, $card);

        event(new PlayerHasPlayedCard($game, $player));
    }
}
