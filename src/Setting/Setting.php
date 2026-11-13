<?php

declare(strict_types=1);

namespace SightPress\Module\Setting;

use SightPress\Module\Exception\InvalidValueException;
use SightPress\Module\Internal\Guard;

/**
 * A per-site setting of a module.
 *
 * Core renders it as a labelled form control, links the help text with `aria-describedby`, validates the value
 * against the type and records every change in the audit log. Never use a setting for secrets such as API keys.
 */
final readonly class Setting
{
    /**
     * At least two lower-case parts joined by dots, starting with the module's area: `events.page_size`.
     */
    public const string KEY_PATTERN = '/^[a-z][a-z0-9_]*(?:\.[a-z][a-z0-9_]*)+$/';

    /** @var non-empty-string */
    public string $key;

    /** @var non-empty-string */
    public string $label;

    public string|int|bool|null $default;

    /** @var array<string, non-empty-string> */
    public array $choices;

    /**
     * @param string                $key     see {@see KEY_PATTERN}
     * @param string                $label   en-GB label of the form control; core translates it with the
     *                                       module's translations
     * @param SettingType           $type    what kind of value it holds
     * @param string|int|bool|null  $default value for sites that haven't changed it; must fit the type
     *                                       (null means `false` for {@see SettingType::Boolean})
     * @param string                $help    optional en-GB help text shown under the control
     * @param array<string, string> $choices for {@see SettingType::Choice} only: stored value => en-GB label;
     *                                       values must not be whole numbers (PHP turns those keys into ints)
     */
    public function __construct(
        string $key,
        string $label,
        public SettingType $type,
        string|int|bool|null $default = null,
        public string $help = '',
        array $choices = [],
    ) {
        $this->key = Guard::matches(
            $key,
            self::KEY_PATTERN,
            'Setting key',
            'use at least two lower-case words joined by dots, for example "events.page_size"',
        );
        $this->label = Guard::notBlank($label, sprintf('Label of setting "%s"', $key));
        $this->default = $type === SettingType::Boolean && $default === null ? false : $default;
        $this->choices = $this->checkChoices($choices);
        $this->checkDefault();
    }

    /**
     * @param array<mixed> $choices
     *
     * @return array<string, non-empty-string>
     */
    private function checkChoices(array $choices): array
    {
        if ($this->type !== SettingType::Choice) {
            if ($choices !== []) {
                throw new InvalidValueException(sprintf('Only choice settings can have choices ("%s").', $this->key));
            }

            return [];
        }

        if ($choices === []) {
            throw new InvalidValueException(sprintf('Choice setting "%s" needs at least one choice.', $this->key));
        }

        $checked = [];
        foreach ($choices as $value => $label) {
            if (!is_string($value) || $value === '') {
                throw new InvalidValueException(sprintf(
                    'Choice values of setting "%s" must be non-empty strings that are not whole numbers.',
                    $this->key,
                ));
            }
            if (!is_string($label)) {
                throw new InvalidValueException(sprintf(
                    'Label of choice "%s" of setting "%s" must be a string.',
                    $value,
                    $this->key,
                ));
            }
            $checked[$value] = Guard::notBlank(
                $label,
                sprintf('Label of choice "%s" of setting "%s"', $value, $this->key),
            );
        }

        return $checked;
    }

    private function checkDefault(): void
    {
        $default = $this->default;
        $fits = match ($this->type) {
            SettingType::Text, SettingType::LongText => $default === null || is_string($default),
            SettingType::Integer => $default === null || is_int($default),
            SettingType::Boolean => is_bool($default),
            SettingType::Choice => $default === null || (is_string($default) && isset($this->choices[$default])),
        };

        if (!$fits) {
            throw new InvalidValueException(sprintf(
                'Default of setting "%s" does not fit its type "%s"%s.',
                $this->key,
                $this->type->value,
                $this->type === SettingType::Choice ? ' (it must be one of the choices)' : '',
            ));
        }
    }
}
