<?php

namespace App\Interfaces;

use Dynamicbits\Larabit\Traits\ProviderTrait;
use Illuminate\Support\ServiceProvider;

class InterfaceServiceProvider extends ServiceProvider
{
    use ProviderTrait;

    public function boot()
    {
        //
    }

    public function register()
    {
        $this->directories = [
            'Repositories' => __DIR__ . '/Repositories',
            'Services' => __DIR__ . '/Services'
        ];

        $this->up();
    }
}
