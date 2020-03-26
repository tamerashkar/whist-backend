<?php

namespace App\Providers;

use App\Deck;
use App\User;
use App\Observers\UserObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public static $observers = [
        User::class => UserObserver::class
    ];

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(Deck::class, function () {
            return Deck::shuffled();
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        foreach (static::$observers as $model => $observer) {
            $model::observe($observer);
        }
    }
}
