<?php

namespace Dynamicbits\Larabit\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class Make extends Command
{
    protected $signature = 'larabit:make {resource}';

    protected $description = 'Creates service and repository including interfaces for specified model.';

    public function handle()
    {
        if (!File::exists(app_path("Models/{$this->argument('resource')}.php"))) {
            $this->error("{$this->argument('resource')} does not exist in " . app_path('Models'));
        } else {
            $stubs = [
                app_path() . "/Interfaces/Repositories/{$this->argument('resource')}RepositoryInterface.php" => __DIR__ . '/../../stubs/app/Interfaces/Repositories/RepositoryInterface.stub',
                app_path() . "/Interfaces/Services/{$this->argument('resource')}ServiceInterface.php" => __DIR__ . '/../../stubs/app/Interfaces/Services/ServiceInterface.stub',
                app_path() . "/Repositories/{$this->argument('resource')}Repository.php" => __DIR__ . '/../../stubs/app/Repositories/Repository.stub',
                app_path() . "/Services/{$this->argument('resource')}Service.php" => __DIR__ . '/../../stubs/app/Services/Service.stub',
            ];

            foreach ($stubs as $target => $stub) {
                $content = file_get_contents($stub);
                $content = str_replace('{{ $resource }}', $this->argument('resource'), $content);

                $this->createFile($target, $content);
            }

            $this->info("Resource created for {$this->argument('resource')}");
        }
    }

    private function createFile(string $target, string $content)
    {
        if (!File::exists($target)) {
            file_put_contents($target, $content);
        } else {
            $this->warn('File already exists: ' . $target);
        }
    }
}
