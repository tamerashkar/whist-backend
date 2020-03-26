<?php

use App\Card;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGamePlayerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('game_player', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('game_id');
            $table->foreign('game_id')->references('id')->on('games');
            $table->unsignedBigInteger('player_id');
            $table->foreign('player_id')->references('id')->on('players');
            $table->unsignedTinyInteger('team');
            $table->unsignedTinyInteger('position');
            $table->unsignedTinyInteger('bid')->nullable();
            $table->boolean('bid_winner')->default(false);
            $table->enum('suit', Card::suits())->nullable();
            $table->unsignedTinyInteger('value')->nullable();
            $table->boolean('hand_winner')->default(false);
            $table->unsignedTinyInteger('hand_wins')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('game_player');
    }
}
