<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(PlayerSeeder::class);
        $this->call(GameSeeder::class);
        $this->call(GamePlayerSeeder::class);
        $this->call(MessageSeeder::class);
    }
}
