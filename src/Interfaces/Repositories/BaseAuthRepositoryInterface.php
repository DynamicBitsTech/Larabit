<?php

namespace Dynamicbits\Larabit\Interfaces\Repositories;

interface BaseAuthRepositoryInterface
{
    public function auth(array $credentials, bool $remember = false): bool;
    public function hasRole(array $roles): bool;
    public function createApiToken(): string;
    public function revokeApiToken(): bool;
    public function createResetToken(string $email): string;
    public function verifyResetToken(string $email, string $token): bool;
    public function updatePassword(string $email, string $password): ?bool;
    public function deleteResetToken(string $email): void;
    public function storeResetOTP(string $email, string $hashedOtp): bool;
}
