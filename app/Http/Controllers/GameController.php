<?php

namespace App\Http\Controllers;

use App\Game;
use App\GameStatus;
use Illuminate\Http\Request;
use App\Events\GameHasStarted;
use App\Http\Resources\GameResource;
use App\Http\Requests\StoreGameRequest;

class GameController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreGameRequest $request)
    {
        return new GameResource(
            Game::setup([$request->user()->player])->withLazyLoads()
        );
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Game  $game
     * @return \Illuminate\Http\Response
     */
    public function show(Game $game)
    {
        // @todo(tamer) - this sets the event back on game which interferes with process.
        // event(new PlayerHasJoinedLobby($game, auth()->user()->player));

        return new GameResource($game->withLazyLoads());
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Game  $game
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Game $game)
    {
        if ($game->status === GameStatus::TEAM_SELECTION && $request->start) {
            $game->start();

            event(new GameHasStarted($game));
        }

        return $this->show($game);
    }
}
