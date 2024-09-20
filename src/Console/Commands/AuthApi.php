<?php

namespace Dynamicbits\Larabit\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class AuthApi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'larabit:auth-api';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates an AuthApiController and api authentication routes';

    /**
     * Execute the console command.
     */
    private $stubPath = __DIR__ . '/../../../stubs';
    public function handle(): void
    {
        $directories = [
            'Http/Controllers/Api',
            'Http/Requests/Api/Auth',
        ];

        foreach ($directories as $directory) {
            $directory = app_path($directory);
            $this->createDirectory($directory);
        }

        $stubs = [
            'app/Http/Controllers/Api/AuthApiController.stub' => app_path('Http/Controllers/Api/AuthApiController.php'),

            'app/Http/Requests/Api/Auth/LoginApiRequest.stub' => app_path('Http/Requests/Api/Auth/LoginApiRequest.php'),
            'app/Http/Requests/Api/Auth/PasswordNewApiRequest.stub' => app_path('Http/Requests/Api/Auth/PasswordNewApiRequest.php'),
            'app/Http/Requests/Api/Auth/PasswordOtpApiRequest.stub' => app_path('Http/Requests/Api/Auth/PasswordOtpApiRequest.php'),
            'app/Http/Requests/Api/Auth/VerifyOtpApiRequest.stub' => app_path('Http/Requests/Api/Auth/VerifyOtpApiRequest.php'),

            'routes/auth_api.stub' => base_path('routes/auth_api.php')
        ];

        foreach ($stubs as $stub => $target) {
            $this->createFile($target, $stub);
        }

        $this->info('Execution completed.');
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
        if (!File::exists($target)) {
            $content = file_get_contents("$this->stubPath/$stub");
            file_put_contents($target, $content);
        } else {
            $this->warn('File already exists: ' . $target);
        }
    }
}
