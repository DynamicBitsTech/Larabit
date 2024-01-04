<?php

namespace Dynamicbits\Larabit\Interfaces\Services;

use Illuminate\Mail\SentMessage;

interface BaseAuthServiceInterface
{
    public function auth(array $credentials, bool $remember = false, array $roles = [], bool $createApiToken = false): bool|string;
    public function logout(): void;
    public function createApiToken(): string;
    public function revokeApiToken(): bool;
    public function sendResetPasswordLink(string $email): void;
    public function createResetToken(string $email): string;
    public function verifyResetToken(string $email, string $token): bool;
    public function updatePassword(string $email, string $password): ?bool;
    public function deleteResetToken(string $email): void;
    public function createResetOTP(string $email): string|bool;
    public function sendResetOTP(string $email): bool;
    public function verifyResetOTP(string $email, string $otp): bool|string;
    public function resetPassword(string $token, string $email, string $password): bool;
}
