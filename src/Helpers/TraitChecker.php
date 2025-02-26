<?php

namespace Dynamicbits\Larabit\Helpers;

use Dynamicbits\Larabit\Exceptions\MissingTraitException;

class TraitChecker
{
    /**
     * Check if the given model or class has the specified trait.
     *
     * @param object|string $object_or_class The model instance or class name to check.
     * @param string $traitClass The fully qualified name of the trait to check for.
     * @param string|null $exceptionMessage Optional. Custom exception message to use if the trait is not found.
     * @throws MissingTraitException If the specified trait is not used by the model or class.
     */
    public static function has(object|string $object_or_class, string $traitClass, string $exceptionMessage = null): void
    {
        $traits = class_uses($object_or_class);
        if (!in_array($traitClass, $traits)) {
            throw new MissingTraitException($traitClass, $exceptionMessage);
        }
    }

    /**
     * Check if the given model or class has any of the specified traits.
     *
     * @param object|string $object_or_class The model instance or class name to check.
     * @param array $traitClasses An array of fully qualified trait names to check for.
     * @param string|null $exceptionMessage Optional. Custom exception message to use if none of the traits are found.
     * @throws MissingTraitException If none of the specified traits are used by the model or class.
     */
    public static function hasAny(object|string $object_or_class, array $traitClasses, string $exceptionMessage = null): void
    {
        $traits = class_uses($object_or_class);
        foreach ($traitClasses as $traitClass) {
            if (in_array($traitClass, $traits)) {
                return;
            }
        }

        $missingTraits = implode(', ', $traitClasses);
        throw new MissingTraitException($missingTraits, $exceptionMessage);
    }
}
