<?php

namespace Dtc\GridBundle\Annotation;

/**
 * Shared argument validation for the annotation/attribute value objects, so
 * every grid annotation enforces its types the same way and a bad value fails
 * loudly at the configuration boundary rather than deep in the cache writer.
 */
trait ValidatesArguments
{
    /**
     * @param string $name
     */
    private static function assertString($value, $name)
    {
        if (null !== $value && !is_string($value)) {
            throw new \InvalidArgumentException('Grid annotation "'.$name.'" must be a string, got '.self::describeType($value));
        }
    }

    /**
     * @param string $name
     */
    private static function assertBool($value, $name)
    {
        if (!is_bool($value)) {
            throw new \InvalidArgumentException('Grid annotation "'.$name.'" must be a bool, got '.self::describeType($value));
        }
    }

    /**
     * @param string $name
     */
    private static function assertInt($value, $name)
    {
        if (null !== $value && !is_int($value)) {
            throw new \InvalidArgumentException('Grid annotation "'.$name.'" must be an int, got '.self::describeType($value));
        }
    }

    /**
     * @param string[] $allowed
     * @param string   $name
     */
    private static function assertOneOf($value, array $allowed, $name)
    {
        if (null !== $value && !in_array($value, $allowed, true)) {
            throw new \InvalidArgumentException('Grid annotation "'.$name.'" must be one of '.implode(', ', $allowed).', got '.self::describeType($value));
        }
    }

    /**
     * @return string
     */
    private static function describeType($value)
    {
        return is_object($value) ? get_class($value) : gettype($value);
    }
}
