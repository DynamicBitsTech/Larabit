<?php

namespace Dynamicbits\Larabit\Repositories\Auth;

use Illuminate\Support\Facades\Auth;

class AuthRepository implements AuthRepositoryInterface
{
    public function auth($credentials, $remember = false): bool
    {
        return auth()->attempt($credentials, $remember);
    }
}
