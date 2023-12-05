<?php

namespace Dynamicbits\Larabit;

use Dynamicbits\Larabit\Repositories\Auth\AuthRepository;
use Dynamicbits\Larabit\Repositories\Auth\AuthRepositoryInterface;
use Dynamicbits\Larabit\Repositories\Base\BaseRepository;
use Dynamicbits\Larabit\Repositories\Base\BaseRepositoryInterface;
use Dynamicbits\Larabit\Services\Auth\AuthService;
use Dynamicbits\Larabit\Services\Auth\AuthServiceInterface;
use Dynamicbits\Larabit\Services\Base\BaseService;
use Dynamicbits\Larabit\Services\Base\BaseServiceInterface;
use Illuminate\Support\ServiceProvider;

class LarabitServiceProvider extends ServiceProvider
{
    public function boot()
    {
    }

    public function register()
    {
        $toBind = [
            BaseServiceInterface::class => BaseService::class,
            BaseRepositoryInterface::class => BaseRepository::class,
            AuthServiceInterface::class => AuthService::class,
            AuthRepositoryInterface::class => AuthRepository::class,
        ];

        foreach ($toBind as $interface => $implementation) {
            $this->app->bind($interface, $implementation);
        }
    }
}
