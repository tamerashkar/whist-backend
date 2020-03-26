<?php

use App\User;
use App\Player;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class PlayerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Schema::disableForeignKeyConstraints();
        User::truncate();
        Player::truncate();
        Schema::enableForeignKeyConstraints();
        factory(User::class)->create([
            'name' => 'Tamer',
            'email' => 'mail@tamerashkar.com',
            'password' => Hash::make('password')
        ]);
        factory(User::class)->create(['name' => 'Danna'])->player->update(['robot' => true]);
        factory(User::class)->create(['name' => 'Essam'])->player->update(['robot' => true]);
        factory(User::class)->create(['name' => 'Marwan'])->player->update(['robot' => true]);
    }
}
