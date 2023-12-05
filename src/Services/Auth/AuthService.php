<?php

namespace Dynamicbits\Larabit\Services\Auth;

use Dynamicbits\Larabit\Repositories\Auth\AuthRepositoryInterface;

class AuthService implements AuthServiceInterface
{
    public function __construct(
        private AuthRepositoryInterface $auth
    ) {
    }

    public function auth(array $credentials, $remember = false): bool
    {
        return $this->auth->auth($credentials, $remember);
    }
}
