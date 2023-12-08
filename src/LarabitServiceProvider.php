<?php

namespace Dynamicbits\Larabit;

use Dynamicbits\Larabit\Interfaces\Repositories\BaseRepositoryInterface;
use Dynamicbits\Larabit\Interfaces\Services\BaseServiceInterface;
use Dynamicbits\Larabit\Repositories\BaseRepository;
use Dynamicbits\Larabit\Services\BaseService;
use Illuminate\Support\ServiceProvider;

class LarabitServiceProvider extends ServiceProvider
{

    protected $commands = [];

    public function boot()
    {
    }

    public function register()
    {
        $toBind = [
            BaseServiceInterface::class => BaseService::class,
            BaseRepositoryInterface::class => BaseRepository::class,
        ];

        foreach ($toBind as $interface => $implementation) {
            $this->app->bind($interface, $implementation);
        }

        $commands = [
            'Install'
        ];

        foreach ($commands as $command) {
            array_push($this->commands, "Dynamicbits\Larabit\Commands\\$command");
        }

        $this->commands($this->commands);
    }
}
