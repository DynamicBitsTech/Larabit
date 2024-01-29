<?php

namespace Dynamicbits\Larabit\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class Make extends Command
{
    protected $signature = 'larabit:make {entity}';

    protected $description = 'Creates service and repository including interfaces for specified model.';

    public function handle()
    {
        $entity = ucfirst($this->argument('entity'));
        if (!File::exists(app_path("Models/{$entity}.php"))) {
            $this->error("{$entity} does not exist in " . app_path('Models'));
        } else {
            $this->createDirectory(app_path() . "/Http/Requests/{$entity}");

            $stubs = [
                app_path() . "/Interfaces/Repositories/{$entity}RepositoryInterface.php" => __DIR__ . '/../../stubs/app/Interfaces/Repositories/RepositoryInterface.stub',
                app_path() . "/Interfaces/Services/{$entity}ServiceInterface.php" => __DIR__ . '/../../stubs/app/Interfaces/Services/ServiceInterface.stub',
                app_path() . "/Repositories/{$entity}Repository.php" => __DIR__ . '/../../stubs/app/Repositories/Repository.stub',
                app_path() . "/Services/{$entity}Service.php" => __DIR__ . '/../../stubs/app/Services/Service.stub',
                app_path() . "/Http/Requests/{$entity}/Store{$entity}Request.php" =>  __DIR__ . '/../../stubs/app/Http/Requests/Entity/StoreRequest.stub',
                app_path() . "/Http/Requests/{$entity}/Update{$entity}Request.php" =>  __DIR__ . '/../../stubs/app/Http/Requests/Entity/UpdateRequest.stub',
            ];

            foreach ($stubs as $target => $stub) {
                $content = file_get_contents($stub);
                $content = str_replace('{{ $entity }}', $entity, $content);

                $this->createFile($target, $content);
            }

            $this->createRoutesAndController($entity);

            $this->info("Resource created for {$entity}");
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

    private function createDirectory($directory)
    {
        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        } else {
            $this->warn('Directory already exists: ' . $directory);
        }
    }

    private function createRoutesAndController($entity)
    {
        // Get the user-provided entity name and prepare some variables based on it
        $entitySnake = Str::snake($entity);
        $entityCamel = Str::camel($entity);
        $entityCapital = ucwords(str_replace('_', ' ', $entitySnake));
        $entityRoute = Str::pluralStudly(Str::kebab($entitySnake));

        // Define the path to the controller stub file
        $controllerStub = __DIR__ . '/../../stubs/app/Http/Controllers/Controller.stub';

        // Read the content of the controller stub file
        $content = file_get_contents($controllerStub);

        // Replace placeholders in the stub content with actual values
        $content = str_replace('{{ $entity }}', $entity, $content);
        $content = str_replace('{{ $entityCamel }}', $entityCamel, $content);
        $content = str_replace('{{ $varEntityPlural }}', Str::pluralStudly($entityCamel), $content);
        $content = str_replace('{{ $view }}', $entitySnake, $content);
        $content = str_replace('{{ $entityCapital }}', $entityCapital, $content);
        $content = str_replace('{{ $entityRoute }}', $entityRoute, $content);

        // Define the target path for the new controller file
        $target = app_path() . "/Http/Controllers/{$entity}Controller.php";

        // Create the new controller file with the updated content
        $this->createFile($target, $content);

        // append routes
        $this->appendRoutes($entitySnake, $entityCamel, $entityRoute);
    }

    private function appendRoutes($entitySnake, $entityCamel, $entityRoute)
    {
        $entityPascal = ucwords(str_replace('_', '', $entitySnake));
        $routes = file_get_contents(__DIR__ . '/../../stubs/routes/web.stub');
        $routes = str_replace('{{ $entity }}', $entityPascal, $routes);
        $routes = str_replace('{{ $entityCamel }}', $entityCamel, $routes);
        $routes = str_replace('{{ $entityRoute }}', $entityRoute, $routes);
        file_put_contents(base_path() . '/routes/web.php', $routes, FILE_APPEND);
    }
}
