<?php

namespace Dynamicbits\Larabit\Services;

use App\Models\User;
use DateTimeInterface;
use Dynamicbits\Larabit\Helpers\TraitChecker;
use Dynamicbits\Larabit\Notifications\ResetPasswordOTP;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\URL;
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
    public function passwordUpdate(array $credentials): bool
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
    public function loginApi(array $credentials, array|string|null $roles = null, DateTimeInterface|null $expiresAt = null): string|bool
    {
        $authenticated = $this->login($credentials, false, $roles);
        if ($authenticated) {
            return $this->createToken($expiresAt);
        }
        return false;
    }
    public function revokeToken(): void
    {
        $this->hasApiTrait();
        $traits = class_uses(User::class);
        $user = request()->user();
        in_array(\Laravel\Sanctum\HasApiTokens::class, $traits)
            ? $user->currentAccessToken()->delete()
            : $user->token()->revoke();
    }
    public function sendResetOtp(string $email): bool
    {
        $otp = random_int(100000, 999999);
        $saved = $this->saveResetToken($email, $otp);
        if ($saved) {
            TraitChecker::has(User::class, \Illuminate\Notifications\Notifiable::class);
            $this->baseUserService->query->firstWhere('email', $email)?->notify(new ResetPasswordOTP($otp));
            return $saved;
        }
        return false;
    }
    public function verifyResetOtp(string $email, string $otp): false|string
    {
        $user = $this->baseUserService->firstByCriteria(['email' => $email]);
        if ($user) {
            $verified = Password::tokenExists($user, $otp);
            if ($verified) {
                return $this->createSignedUrl('api.password.set-new', params: ['email' => $email]);
            }
        }
        return false;
    }
    public function passwordSetNew(string $email, string $password): bool
    {
        $user = $this->baseUserService->firstByCriteria(['email' => $email]);
        return $this->baseUserService->update($user, ['password' => Hash::make($password)]);
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
    private function createToken(DateTimeInterface|null $expiresAt = null): string
    {
        $this->hasApiTrait();
        $token = Auth::user()->createToken('AuthToken', expiresAt: $expiresAt);
        $traits = class_uses(User::class);
        return in_array(\Laravel\Sanctum\HasApiTokens::class, $traits) ? $token->plainTextToken : $token->accessToken;
    }
    private function hasApiTrait(): void
    {
        TraitChecker::hasAny(User::class, [
            \Laravel\Sanctum\HasApiTokens::class,
            \Laravel\Passport\HasApiTokens::class
        ]);
    }
    private function saveResetToken(string $email, $token): bool
    {
        return DB::table('password_reset_tokens')
            ->updateOrInsert(
                ['email' => $email],
                ['token' => Hash::make($token), 'created_at' => now()]
            );
    }
    private function createSignedUrl($name, $expiration = 60, array $params = []): string
    {
        return URL::temporarySignedRoute($name, $expiration, $params);
    }
}
