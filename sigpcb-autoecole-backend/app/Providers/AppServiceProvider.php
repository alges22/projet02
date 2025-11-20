<?php

namespace App\Providers;

use Laravel\Sanctum\Sanctum;
use App\Models\AutoEcoleAccesToken;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Sanctum::usePersonalAccessTokenModel(AutoEcoleAccesToken::class);
    }
}
