<?php

declare(strict_types=1);

namespace SightPress\Module\Internal;

use SightPress\Module\Exception\InvalidValueException;

/**
 * Shared checks for the value objects in this package.
 *
 * @internal Not part of the contract: it may change in any release. Modules must not use it.
 */
final class Guard
{
    /**
     * A dotted, lower-case name such as `page.edit.own` or `blog.posts`.
     */
    public const string DOTTED_NAME = '/^[a-z][a-z0-9_]*(?:\.[a-z][a-z0-9_]*)*$/';

    /**
     * @return non-empty-string
     */
    public static function notBlank(string $value, string $field): string
    {
        if (trim($value) === '') {
            throw new InvalidValueException(sprintf('%s must not be empty.', $field));
        }

        return $value;
    }

    /**
     * @param non-empty-string $pattern
     *
     * @return non-empty-string
     */
    public static function matches(string $value, string $pattern, string $field, string $rule): string
    {
        if ($value === '' || preg_match($pattern, $value) !== 1) {
            throw new InvalidValueException(sprintf('%s is not valid ("%s"): %s.', $field, $value, $rule));
        }

        return $value;
    }

    /**
     * @param list<string> $values
     */
    public static function unique(array $values, string $field): void
    {
        $duplicates = array_keys(array_filter(array_count_values($values), static fn (int $count): bool => $count > 1));
        if ($duplicates !== []) {
            throw new InvalidValueException(sprintf('%s has duplicates: %s.', $field, implode(', ', $duplicates)));
        }
    }

    /**
     * Checks at run time what the `list<T>` type of a parameter promises, for callers without static analysis.
     *
     * @template T of object
     *
     * @param array<mixed>    $values
     * @param class-string<T> $class
     *
     * @return list<T>
     */
    public static function listOf(array $values, string $class, string $field): array
    {
        if (!array_is_list($values)) {
            throw new InvalidValueException(sprintf('%s must be a list.', $field));
        }

        foreach ($values as $index => $value) {
            if (!$value instanceof $class) {
                throw new InvalidValueException(sprintf('%s[%d] must be an instance of %s.', $field, $index, $class));
            }
        }

        /** @var list<T> $values */
        return $values;
    }

    /**
     * @param array<mixed> $values
     *
     * @return list<string>
     */
    public static function listOfStrings(array $values, string $field): array
    {
        if (!array_is_list($values)) {
            throw new InvalidValueException(sprintf('%s must be a list.', $field));
        }

        foreach ($values as $index => $value) {
            if (!is_string($value)) {
                throw new InvalidValueException(sprintf('%s[%d] must be a string.', $field, $index));
            }
        }

        /** @var list<string> $values */
        return $values;
    }
}
