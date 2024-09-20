<?php

namespace Dynamicbits\Larabit;

use Dynamicbits\Larabit\Console\Commands\Auth;
use Dynamicbits\Larabit\Console\Commands\AuthApi;
use Dynamicbits\Larabit\Console\Commands\Service;
use Illuminate\Support\Facades\Route;
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
            Auth::class,
            AuthApi::class
        ]);

        $authRouter = base_path('routes/auth.php');
        $authApiRouter = base_path('routes/auth_api.php');

        if (file_exists($authRouter)) {
            $this->loadRoutesFrom($authRouter);
        }
        if (file_exists($authApiRouter)) {
            Route::middleware('api')
                ->prefix('api')
                ->group(function () use ($authApiRouter) {
                    require $authApiRouter;
                });
        }
    }
}
