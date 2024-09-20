<?php

namespace Dynamicbits\Larabit\Helpers;

use Dynamicbits\Larabit\Exceptions\MissingTraitException;
use Exception;
use Illuminate\Database\Eloquent\Model;

class TraitChecker
{
    /**
     * Check if the given model has the specified trait.
     *
     * @param Model $model
     * @param string $traitClass
     * @param string|null $exceptionMessage
     * @throws Exception
     */
    public static function has($model, string $traitClass, string $exceptionMessage = null): void
    {
        $traits = class_uses($model);
        if (!in_array($traitClass, $traits)) {
            throw new MissingTraitException($traitClass, $exceptionMessage);
        }
    }
    public static function hasAny($model, array $traitClasses, string $exceptionMessage = null): void
    {
        $traits = class_uses($model);
        foreach ($traitClasses as $traitClass) {
            if (in_array($traitClass, $traits)) {
                return;
            }
        }

        $missingTraits = implode(', ', $traitClasses);
        throw new MissingTraitException($missingTraits, $exceptionMessage);
    }
}
