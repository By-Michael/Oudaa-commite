<?php

namespace App\Providers;

use App\Support\CurrentCommunity;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(CurrentCommunity::class);
    }

    public function boot()
    {
        //
    }
}
