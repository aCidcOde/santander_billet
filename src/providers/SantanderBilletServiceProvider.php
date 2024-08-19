<?php

namespace FlyCorp\SantanderBillet\providers;

use Illuminate\Support\ServiceProvider;

class SantanderBilletServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/santander_billet.php' => config_path('santander_billet.php'),
        ], 'config');
    }

    public function register()
    {

    }
}
