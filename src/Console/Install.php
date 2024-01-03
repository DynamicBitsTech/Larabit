<?php

namespace Dynamicbits\Larabit\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class Install extends Command
{
    protected $signature = 'larabit:install';

    protected $description = 'Creates essential directories and the InterfaceServiceProvider in your application.';

    public function handle()
    {
        $this->info('Installing Larabit...');

        $directories = [
            'Http/Controllers/Api',
            'Http/Requests/Api',
            'Http/Requests/Auth',
            'Interfaces/Services',
            'Interfaces/Repositories',
            'Repositories',
            'Services',
        ];

        foreach ($directories as $directory) {
            $directory = app_path($directory);
            $this->createDirectory($directory);
        }

        $stubs = [
            'app/Http/Controllers/AuthController.stub'                  =>  app_path('Http/Controllers/AuthController.php'),
            'app/Http/Controllers/Api/AuthApiController.stub'           =>  app_path('Http/Controllers/Api/AuthApiController.php'),
            'app/Http/Requests/Auth/AuthRequest.stub'                   =>  app_path('Http/Requests/Auth/AuthRequest.php'),
            'app/Http/Requests/Auth/PasswordEmailRequest.stub'          =>  app_path('Http/Requests/Auth/PasswordEmailRequest.php'),
            'app/Http/Requests/Auth/PasswordResetRequest.stub'          =>  app_path('Http/Requests/Auth/PasswordResetRequest.php'),
            'app/Http/Requests/Api/AuthApiRequest.stub'                 =>  app_path('Http/Requests/Api/AuthApiRequest.php'),
            'app/Http/Requests/Api/PasswordOtpApiRequest.stub'          =>  app_path('Http/Requests/Api/PasswordOtpApiRequest.php'),
            'app/Http/Requests/Api/PasswordResetApiRequest.stub'        =>  app_path('Http/Requests/Api/PasswordResetApiRequest.php'),
            'app/Http/Requests/Api/PasswordVerifyOtpApiRequest.stub'    =>  app_path('Http/Requests/Api/PasswordVerifyOtpApiRequest.php'),
            'app/Interfaces/Repositories/AuthRepositoryInterface.stub'  =>  app_path('Interfaces/Repositories/AuthRepositoryInterface.php'),
            'app/Interfaces/Services/AuthServiceInterface.stub'         =>  app_path('Interfaces/Services/AuthServiceInterface.php'),
            'app/Repositories/AuthRepository.stub'                      =>  app_path('Repositories/AuthRepository.php'),
            'app/Services/AuthService.stub'                             =>  app_path('Services/AuthService.php'),
            'app/Interfaces/InterfaceServiceProvider.stub'              =>  app_path('Interfaces/InterfaceServiceProvider.php'),
            'routes/auth.stub'                                          =>  base_path('routes/auth.php')
        ];

        foreach ($stubs as $stub => $target) {
            $this->createFile($target, $stub);
        }

        $this->info('Larabit has been installed.');
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
            $content = file_get_contents(__DIR__ . "/../../stubs/{$stub}");
            file_put_contents($target, $content);
        } else {
            $this->warn('File already exists: ' . $target);
        }
    }
}
