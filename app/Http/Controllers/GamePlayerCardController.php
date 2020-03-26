<?php

namespace App\Http\Controllers;

use App\Game;
use Illuminate\Http\Request;
use App\Http\Resources\CardResource;

class GamePlayerCardController extends Controller
{
    public function index(Game $game, Request $request)
    {
        $player = $request->user()->player;

        return CardResource::collection($player->cardsForGame($game)->orderBy('suit')->orderBy('value')->get());
    }
}
