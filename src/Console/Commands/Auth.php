<?php

namespace Dynamicbits\Larabit\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class Auth extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'larabit:auth';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates an AuthController, authentication routes, and associated services.';

    /**
     * Execute the console command.
     */
    private $stubPath = __DIR__ . '/../../../stubs';
    public function handle(): void
    {
        $directories = [
            'Http/Requests/Auth',
            'Services',
        ];

        foreach ($directories as $directory) {
            $directory = app_path($directory);
            $this->createDirectory($directory);
        }

        $stubs = [
            'app/Http/Controllers/AuthController.stub' => app_path('Http/Controllers/AuthController.php'),
            'app/Services/AuthService.stub' => app_path('Services/AuthService.php'),
            'app/Services/UserService.stub' => app_path('Services/UserService.php'),

            'app/Http/Requests/Auth/LoginRequest.stub' => app_path('Http/Requests/Auth/LoginRequest.php'),
            'app/Http/Requests/Auth/PasswordEmailRequest.stub' => app_path('Http/Requests/Auth/PasswordEmailRequest.php'),
            'app/Http/Requests/Auth/PasswordResetRequest.stub' => app_path('Http/Requests/Auth/PasswordResetRequest.php'),

            'routes/auth.stub' => base_path('routes/auth.php')
        ];

        foreach ($stubs as $stub => $target) {
            $this->createFile($target, $stub);
        }

        $this->warn('File already exists: ' . $target);
    }

    private function createDirectory($directory)
    {
        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        } else {
            $this->warn('Directory already exists: ' . $directory);
        }
    }

    private function createFile($target, $stub)
    {
        if (!File::exists(path: $target)) {
            $content = file_get_contents("$this->stubPath/$stub");
            file_put_contents($target, $content);
        } else {
            $this->warn('File already exists: ' . $target);
        }
    }
}
