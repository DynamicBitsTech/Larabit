<?php

namespace Dynamicbits\Larabit\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class Service extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'larabit:service {entity}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a BaseService extended service for a model';

    private string $namespace = 'App\Services';
    private string $className;
    private string $modelNamespace = '\App\Models';
    private string $contents;

    /**
     * The path to the stub file.
     */
    private const STUB_PATH = __DIR__ . '/../../../stubs/app/Services/EntityService.stub';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $entity = $this->argument('entity');
        if (!$this->processEntity($entity)) {
            $this->error('Failed to process entity input.');
            return;
        }

        if (!$this->prepareContent()) {
            $this->error('Could not prepare the content from the stub.');
            return;
        }

        if (!$this->saveContent()) {
            $this->error('Could not create the file.');
        } else {
            $this->info('Service created successfully.');
        }
    }

    /**
     * Processes the input entity to build namespace and model name.
     *
     * @param string $entity
     * @return bool
     */
    private function processEntity(string $entity): bool
    {
        $normalizedEntity = str_replace('\\', '/', $entity);
        $parts = explode('/', $normalizedEntity);
        $modelName = array_pop($parts);
        $subNamespace = implode('\\', $parts);

        if ($subNamespace) {
            $this->namespace .= "\\$subNamespace";
            $this->modelNamespace .= "\\$subNamespace";
        }

        $this->className = "{$modelName}Service";
        $this->modelNamespace .= "\\$modelName";

        return !empty($this->className) && !empty($this->modelNamespace);
    }

    /**
     * Prepares the content by replacing placeholders in the stub.
     *
     * @return bool
     */
    private function prepareContent(): bool
    {
        if (!file_exists(self::STUB_PATH)) {
            $this->error('Stub file does not exist.');
            return false;
        }

        $stubContent = file_get_contents(self::STUB_PATH);

        if ($stubContent === false) {
            return false;
        }

        $this->contents = str_replace(
            ['NAMESPACE', 'CLASS_NAME', 'MODEL'],
            [$this->namespace, $this->className, $this->modelNamespace],
            $stubContent
        );

        return true;
    }

    /**
     * Saves the content to the appropriate service file.
     *
     * @return bool
     */
    private function saveContent(): bool
    {
        $directoryPath = base_path(str_replace('\\', '/', $this->namespace));

        if (!File::exists($directoryPath)) {
            if (!File::makeDirectory($directoryPath, 0755, true)) {
                return false;
            }
        }

        $filePath = "$directoryPath/{$this->className}.php";

        if (File::exists($filePath)) {
            $this->warn("File already exists: {$filePath}");
            return false;
        }

        return File::put($filePath, $this->contents) !== false;
    }
}
