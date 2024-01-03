<?php

namespace Dynamicbits\Larabit\Repositories;

use App\Models\User;
use Dynamicbits\Larabit\Interfaces\Repositories\BaseAuthRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class BaseAuthRepository implements BaseAuthRepositoryInterface
{
    private BaseRepository $userRepository;
    public function __construct(User $model)
    {
        $this->userRepository = new BaseRepository($model);
    }

    public function auth(array $credentials, bool $remember = false): bool
    {
        return auth()->attempt($credentials, $remember);
    }

    public function hasRole(array $roles): bool
    {
        return in_array($roles, auth()->user()->roles, true);
    }

    public function createApiToken(): string
    {
        return auth()->user()->createToken('auth_token')->accessToken;
    }

    public function revokeApiToken(): bool
    {
        return auth()->user()->token()->revoke();
    }

    public function createResetToken(string $email): string
    {
        $user = $this->userRepository->findByCriteria(['email' => $email]);
        return Password::createToken($user);
    }

    public function verifyResetToken(string $email, string $token): bool
    {
        $user = $this->userRepository->findByCriteria(['email' => $email]);
        return Password::tokenExists($user, $token);
    }

    public function updatePassword(string $email, string $password): ?bool
    {
        $user = $this->userRepository->findByCriteria(['email' => $email]);
        return $this->userRepository->update($user, ['password' => $password]);
    }

    public function deleteResetToken(string $email): void
    {
        $user = $this->userRepository->findByCriteria(['email' => $email]);
        Password::deleteToken($user);
    }

    public function storeResetOTP(string $email, string $hashedOtp): bool
    {
        return DB::table('password_reset_tokens')
            ->updateOrInsert(
                ['email' => $email],
                ['token' => $hashedOtp, 'created_at' => now()]
            );
    }
}
