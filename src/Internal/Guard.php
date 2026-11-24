<?php

declare(strict_types=1);

namespace SightPress\Module\Internal;

use DateTimeImmutable;
use DateTimeZone;
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
     * A BCP 47 language tag in its usual casing: language, optional script, optional region, for example `en-GB`,
     * `pt-PT`, `zh-Hant`, `zh-Hant-TW` or `es-419`.
     */
    public const string LOCALE = '/^[a-z]{2,3}(?:-[A-Z][a-z]{3})?(?:-(?:[A-Z]{2}|\d{3}))?$/';

    /** Human-readable form of {@see LOCALE}, used in error messages. */
    public const string LOCALE_RULE = 'use a language tag such as "en-GB", "pt-PT" or "zh-Hant"';

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
     * @return non-empty-string
     */
    public static function locale(string $value, string $field): string
    {
        return self::matches($value, self::LOCALE, $field, self::LOCALE_RULE);
    }

    public static function notNegative(int $value, string $field): int
    {
        if ($value < 0) {
            throw new InvalidValueException(sprintf('%s must not be negative (%d).', $field, $value));
        }

        return $value;
    }

    /**
     * The same moment in UTC, so every date in a contract is in UTC whatever the caller passed.
     */
    public static function utc(DateTimeImmutable $value): DateTimeImmutable
    {
        return $value->setTimezone(new DateTimeZone('UTC'));
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

    /**
     * @param array<mixed> $values
     *
     * @return list<int<0, max>>
     */
    public static function listOfIds(array $values, string $field): array
    {
        if (!array_is_list($values)) {
            throw new InvalidValueException(sprintf('%s must be a list.', $field));
        }

        foreach ($values as $index => $value) {
            if (!is_int($value) || $value < 0) {
                throw new InvalidValueException(sprintf('%s[%d] must be an id (a whole number, 0 or more).', $field, $index));
            }
        }

        /** @var list<int<0, max>> $values */
        return $values;
    }

    /**
     * A template of a module, in its view namespace.
     *
     * @return non-empty-string
     */
    public static function template(string $template): string
    {
        return self::matches(
            $template,
            '#^@[a-z][a-z0-9_]*/(?!.*\.\./)\S+\.twig$#',
            'Template',
            'use a template of the module\'s view namespace, for example "@acme_events/list.html.twig"',
        );
    }

    /**
     * Template variables: keyed by name.
     *
     * @param array<mixed> $context
     *
     * @return array<string, mixed>
     */
    public static function context(array $context): array
    {
        foreach (array_keys($context) as $name) {
            if (!is_string($name) || preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $name) !== 1) {
                throw new InvalidValueException(sprintf('Template variable names must be words such as "events" ("%s").', $name));
            }
        }

        /** @var array<string, mixed> $context */
        return $context;
    }

    public static function status(int $status): int
    {
        if ($status < 200 || $status > 599 || ($status >= 300 && $status < 400)) {
            throw new InvalidValueException(sprintf('HTTP status must be 2xx, 4xx or 5xx (%d): redirect with Screens::redirect().', $status));
        }

        return $status;
    }

    /**
     * An ISO 3166-1 alpha-2 country code, upper-cased.
     *
     * @return non-empty-string
     */
    public static function country(string $country): string
    {
        return self::matches(strtoupper($country), '/^[A-Z]{2}$/', 'Country', 'use a two-letter ISO 3166-1 code such as "GB"');
    }
}
