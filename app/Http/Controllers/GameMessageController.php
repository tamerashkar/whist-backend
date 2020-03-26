<?php

namespace App\Http\Controllers;

use App\Game;
use App\Message;
use App\Events\MessageWasCreated;
use App\Http\Resources\MessageResource;
use App\Http\Requests\StoreMessageRequest;

class GameMessageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Game $game)
    {
        return MessageResource::collection(
            Message::where('game_id', $game->id)
                ->with(Message::eagerLoadsFor($game))
                ->orderByDesc('id')
                ->get()
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreMessageRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreMessageRequest $request, Game $game)
    {
        $message = Message::create([
            'game_id' => $game->id,
            'body' => $request->body,
            'player_id' => $request->user()->id,
        ]);

        event(new MessageWasCreated($message));

        return new MessageResource($message);
    }
}
