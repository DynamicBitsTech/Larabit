<?php

namespace Dynamicbits\Larabit\Exceptions;

use Exception;

class MissingTraitException extends Exception
{
    public function __construct(string $traitClass, string $message = null)
    {
        $this->message = $message ? $message : "The model does not use the required trait: $traitClass";
    }
}
