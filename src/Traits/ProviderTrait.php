<?php

namespace Dynamicbits\Larabit\Traits;

use Illuminate\Support\Str;

trait ProviderTrait
{
    protected array $directories = [];

    private array $toBind = [];

    protected string $namespace = 'App';

    protected function up()
    {
        $this->getServiceInterfaces();
        $this->getRepositoryInterfaces();
        $this->bindInterfacesAndImplementations();
    }

    private function getServiceInterfaces(): void
    {
        $this->getInterfacesFromDirectory(
            $this->directories['Services'],
            'Service'
        );
    }

    private function getRepositoryInterfaces(): void
    {
        $this->getInterfacesFromDirectory(
            $this->directories['Repositories'],
            'Repository'
        );
    }

    private function getInterfacesFromDirectory($directory, $suffix): void
    {
        foreach (glob("$directory/*{$suffix}Interface.php") as $file) {
            $resource = Str::of($file)
                ->replace("$directory/", '')
                ->replace("{$suffix}Interface.php", '')
                ->studly();
            $resourceParent = Str::plural($suffix);

            $this->toBind = array_merge($this->toBind, [
                "$this->namespace\\Interfaces\\$resourceParent\\{$resource}{$suffix}Interface" => "$this->namespace\\$resourceParent\\$resource$suffix"
            ]);
        }
    }

    private function bindInterfacesAndImplementations(): void
    {
        foreach ($this->toBind as $interface => $implementation) {
            $this->app->bind($interface, $implementation);
        }
    }
}
