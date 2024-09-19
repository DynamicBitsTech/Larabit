<?php

namespace Dynamicbits\Larabit\Services;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;

abstract class BaseAuthService
{
    public function __construct(
        private BaseUserService $baseUserService
    ) {
    }
    public function login(array $credentials, $remember = false, array|string|null $roles = null, string $authColumn = 'email'): bool
    {
        $authenticated = $this->authenticate($credentials, $remember, $authColumn);
        if (!empty($roles) && !$this->baseUserService->hasRole($roles)) {
            $this->logout();
            return false;
        }
        return $authenticated;
    }
    public function logout(): void
    {
        Auth::logout();
        Request::session()->invalidate();
        Request::session()->regenerate();
    }
    public function passwordEmail(string $email): bool
    {
        return Password::RESET_LINK_SENT === Password::sendResetLink(['email' => $email]);
    }
    public function passwordUpdate(array $credentials)
    {
        $status = Password::reset(
            $credentials,
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));
                $user->save();
                event(new PasswordReset($user));
            }
        );
        return $status === Password::PASSWORD_RESET;
    }
    private function authenticate(array $credentials, bool $remember, $authColumn = 'email'): bool
    {
        if ($authColumn == 'email') {
            return Auth::attempt($credentials, $remember);
        }
        $user = $this->baseUserService->query->where($authColumn, $credentials[$authColumn] ?? ($credentials['email']) ?? '')->first();
        if ($user && Hash::check($credentials['password'] ?? '', $user->password)) {
            Auth::login($user);
            return true;
        }
        return false;
    }
}
