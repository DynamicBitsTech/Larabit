<?php

namespace Dynamicbits\Larabit\Services\Auth;

interface AuthServiceInterface
{
    public function auth(array $credentials, $remember = false): bool;
}
