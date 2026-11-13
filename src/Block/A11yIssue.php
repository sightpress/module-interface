<?php

declare(strict_types=1);

namespace SightPress\Module\Block;

use SightPress\Module\Internal\Guard;

/**
 * An accessibility problem found in one block's data by an {@see A11yRule}.
 */
final readonly class A11yIssue
{
    /** @var non-empty-string */
    public string $rule;

    /** @var non-empty-string */
    public string $message;

    /** @var non-empty-string|null */
    public ?string $field;

    /** @var list<non-empty-string> */
    public array $wcag;

    /**
     * @param string       $rule     id of the rule that found it, for example `image.alt_missing`
     * @param string       $message  plain en-GB sentence that tells the editor how to fix it, for example
     *                               "Describe the image, or mark it as decorative."; core translates it with the
     *                               module's translations
     * @param A11ySeverity $severity errors block publishing; warnings don't
     * @param string|null  $field    JSON Pointer (RFC 6901) to the field in the block data, for example `/alt`,
     *                               so the editor can move focus to it; null for the block as a whole
     * @param list<string> $wcag     WCAG success criteria it breaks, for example `1.1.1`
     */
    public function __construct(
        string $rule,
        string $message,
        public A11ySeverity $severity = A11ySeverity::Error,
        ?string $field = null,
        array $wcag = [],
    ) {
        $this->rule = Guard::matches(
            $rule,
            Guard::DOTTED_NAME,
            'Accessibility rule id',
            'use lower-case words joined by dots, for example "image.alt_missing"',
        );
        $this->message = Guard::notBlank($message, sprintf('Message of accessibility issue "%s"', $rule));
        $this->field = $field === null ? null : Guard::matches(
            $field,
            '/^(?:\/(?:[^~\/]|~[01])*)+$/',
            sprintf('Field of accessibility issue "%s"', $rule),
            'use a JSON Pointer such as "/alt"',
        );

        $criteria = [];
        foreach (Guard::listOfStrings($wcag, sprintf('WCAG criteria of "%s"', $rule)) as $criterion) {
            $criteria[] = Guard::matches(
                $criterion,
                '/^[1-4]\.[1-9][0-9]?\.[1-9][0-9]?$/',
                sprintf('WCAG criterion of accessibility issue "%s"', $rule),
                'use a success criterion number such as "1.1.1"',
            );
        }
        Guard::unique($criteria, sprintf('WCAG criteria of "%s"', $rule));
        $this->wcag = $criteria;
    }
}
