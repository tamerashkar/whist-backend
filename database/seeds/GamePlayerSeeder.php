<?php

use App\Game;
use App\GamePlayer;
use App\Player;
use Illuminate\Database\Seeder;

class GamePlayerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        GamePlayer::truncate();

        $game = Game::first();

        foreach (Player::all() as $key => $player) {
            $player->join($game, $key % 2 + 1);
        }
    }
}
