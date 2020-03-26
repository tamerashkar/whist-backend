<?php

use App\Card;
use App\GameStatus;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGamesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->string('event')->default('Init');
            GameStatus::create($table, 'status');
            $table->unsignedTinyInteger('points_to_win')->default(52);
            $table->tinyInteger('home_team_points')->default(0);
            $table->tinyInteger('guest_team_points')->default(0);
            $table->unsignedTinyInteger('start_turn')->default(1);
            $table->unsignedTinyInteger('next_turn')->default(1);
            $table->unsignedTinyInteger('dealer_position')->default(null)->nullable();
            $table->enum('trump_suit', Card::suits())->nullable();
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
        Schema::dropIfExists('games');
    }
}
