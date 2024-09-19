<?php

namespace Dynamicbits\Larabit\Exceptions;

use Exception;

class SoftDeleteNotAppliedException extends Exception
{
    public function __construct($model)
    {
        $this->message = "Soft delete is not applied on model: " . get_class($model);
    }
}
