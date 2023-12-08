<?php

namespace Dynamicbits\Larabit\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class Install extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'larabit:install';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $directories = [
            'Interfaces',
            'Repositories',
            'Services',
        ];

        $subDirectories = [
            'Interfaces' => [
                'Services',
                'Repositories',
            ]
        ];

        foreach ($directories as $directory) {
            $directoryPath = app_path($directory);
            if (!File::exists($directoryPath)) {
                File::makeDirectory($directoryPath, 0755, true);
            } else {
                $this->warn('Directory already exists: ' . $directoryPath);
            }
        }

        foreach ($subDirectories as $parent => $children) {
            // dd($children);
            foreach ($children as $child) {
                $directoryPath = app_path($parent) . '/' . $child;
                if (!File::exists($directoryPath)) {
                    File::makeDirectory($directoryPath, 0755, true);
                } else {
                    $this->warn('Directory already exists: ' . $directoryPath);
                }
            }
        }

        $content = file_get_contents(__DIR__ . '/stubs/interface-service-provider.stub');
        file_put_contents(app_path() . '/Interfaces/InterfaceServiceProvider.php', $content);
    }
}
