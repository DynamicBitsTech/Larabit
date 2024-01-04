<?php

namespace Dynamicbits\Larabit\Services;

use App\Models\User;
use Dynamicbits\Larabit\Interfaces\Repositories\BaseAuthRepositoryInterface;
use Dynamicbits\Larabit\Interfaces\Services\BaseAuthServiceInterface;
use Dynamicbits\Larabit\Notifications\ResetPasswordLink;
use Dynamicbits\Larabit\Notifications\ResetPasswordOTP;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Hash;

class BaseAuthService implements BaseAuthServiceInterface
{
    public function __construct(
        private BaseAuthRepositoryInterface $iAuthRepository
    ) {
    }

    public function auth(array $credentials, bool $remember = false, array $roles = [], bool $createApiToken = false): bool|string
    {
        $authenticated = $this->iAuthRepository->auth($credentials, $remember);

        if (!$authenticated) {
            return false;
        }

        if (!empty($roles) && !$this->iAuthRepository->hasRole($roles)) {
            if (!$createApiToken) {
                $this->logout();
            }
            return false;
        }

        if ($createApiToken) {
            return $this->createApiToken();
        }

        return true;
    }

    public function createApiToken(): string
    {
        return $this->iAuthRepository->createApiToken();
    }

    public function logout(): void
    {
        auth()->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
    }

    public function revokeApiToken(): bool
    {
        return $this->iAuthRepository->revokeApiToken();
    }

    public function sendResetPasswordLink(string $email): void
    {
        $token = $this->createResetToken($email);
        $url = URL::temporarySignedRoute('password.reset', now()->addHour(), ['token' => $token, 'email' => $email]);
        User::whereEmail($email)->first()->notify(new ResetPasswordLink($url));
    }

    public function createResetToken(string $email): string
    {
        return $this->iAuthRepository->createResetToken($email);
    }

    public function verifyResetToken(string $email, string $token): bool
    {
        return $this->iAuthRepository->verifyResetToken($email, $token);
    }

    public function updatePassword(string $email, string $password): ?bool
    {
        return $this->iAuthRepository->updatePassword($email, $password);
    }

    public function deleteResetToken(string $email): void
    {
        $this->iAuthRepository->deleteResetToken($email);
    }

    public function createResetOTP(string $email): string|bool
    {
        $otp = rand(100000, 999999);

        $hashedOtp = Hash::make($otp);

        $otpSaved = $this->iAuthRepository->storeResetOTP($email, $hashedOtp);

        return $otpSaved ? $otp : $otpSaved;
    }

    public function sendResetOTP(string $email): bool
    {
        $otp = $this->createResetOTP($email);

        $isBool = is_bool($otp);

        if (!$isBool) {
            User::whereEmail($email)->first()->notify(new ResetPasswordOTP($otp));
        }

        return !$isBool ? true : false;
    }

    public function verifyResetOTP(string $email, string $otp): bool|string
    {
        $isValid = $this->verifyResetToken($email, $otp);

        return $isValid ? URL::temporarySignedRoute('password.reset.api', now()->addMinutes(5), ['email' => $email]) : $isValid;
    }

    public function resetPassword(string $token, string $email, string $password): bool
    {
        $isValid = $this->verifyResetToken($email, $token);

        return $isValid ? $this->updatePassword($email, $password) : $isValid;
    }
}
