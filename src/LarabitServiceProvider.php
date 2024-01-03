<?php

namespace Dynamicbits\Larabit;

use Dynamicbits\Larabit\Traits\ProviderTrait;
use Illuminate\Support\ServiceProvider;

class LarabitServiceProvider extends ServiceProvider
{
    use ProviderTrait;

    private array $commands = [];

    public function boot()
    {
        //
    }

    public function register()
    {
        $this->directories = [
            'Repositories' => __DIR__ . '/Interfaces/Repositories',
            'Services' => __DIR__ . '/Interfaces/Services'
        ];

        $this->namespace = 'Dynamicbits\Larabit';

        $this->up();

        $commands = [
            'Install',
            'Make',
        ];

        foreach ($commands as $command) {
            array_push($this->commands, "Dynamicbits\Larabit\Console\\$command");
        }

        $this->commands($this->commands);

        $routerPath = base_path('routes/auth.php');

        if (file_exists($routerPath)) {
            $this->loadRoutesFrom($routerPath);
        }
    }
}
