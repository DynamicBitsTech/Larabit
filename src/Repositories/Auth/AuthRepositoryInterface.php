<?php

namespace Dynamicbits\Larabit\Repositories\Auth;

interface AuthRepositoryInterface
{
    /**
     * @param array $credentials Associative array containing 'email' and 'password'
     * @param bool $remember
     * @return bool
     */
    public function auth(array $credentials, bool $remember = false): bool;
}
