<?php

namespace Dynamicbits\Larabit\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class Install extends Command
{
    protected $signature = 'larabit:install';

    protected $description = 'Creates essential directories and the InterfaceServiceProvider in your application.';

    public function handle()
    {
        $directories = [
            'Interfaces' => [
                'Services',
                'Repositories',
            ],
            'Repositories' => [],
            'Services' => [],
        ];

        foreach ($directories as $dir => $subDirs) {
            $directoryPath = app_path($dir);
            $this->createDirectory($directoryPath);

            foreach ($subDirs as $subDir) {
                $this->createDirectory($directoryPath . '\\' . $subDir);
            }
        }

        $filePath = app_path('Interfaces/InterfaceServiceProvider.php');

        if (!File::exists($filePath)) {
            $content = file_get_contents(__DIR__ . '/stubs/interface-service-provider.stub');
            file_put_contents($filePath, $content);
        } else {
            $this->warn('File already exists: ' . $filePath);
        }
    }

    private function createDirectory($directoryPath)
    {
        if (!File::exists($directoryPath)) {
            File::makeDirectory($directoryPath, 0755, true);
        } else {
            $this->warn('Directory already exists: ' . $directoryPath);
        }
    }
}
