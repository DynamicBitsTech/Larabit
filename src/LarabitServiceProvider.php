<?php

namespace Dynamicbits\Larabit;

use Dynamicbits\Larabit\Console\Commands\Auth;
use Dynamicbits\Larabit\Console\Commands\Service;
use Illuminate\Support\ServiceProvider;

class LarabitServiceProvider extends ServiceProvider
{
    public function boot()
    {
        //
    }

    public function register()
    {
        $this->commands([
            Service::class,
            Auth::class
        ]);

        $routerPath = base_path('routes/auth.php');

        if (file_exists($routerPath)) {
            $this->loadRoutesFrom($routerPath);
        }
    }
}
