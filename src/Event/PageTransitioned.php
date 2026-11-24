<?php

declare(strict_types=1);

namespace SightPress\Module\Event;

use SightPress\Module\Exception\InvalidValueException;
use SightPress\Module\Internal\Guard;

/**
 * A page went through a workflow step: sent for review, approved, published, unpublished, archived, put in the
 * trash … Part of the stable event list (1.0 API).
 *
 * The step and the states are plain strings, so modules don't depend on core's enums; the constants list every
 * value core uses.
 */
final readonly class PageTransitioned
{
    /** Every workflow step, as {@see $transition}. */
    public const array TRANSITIONS = ['submit', 'reject', 'approve', 'publish', 'unpublish', 'archive', 'restore', 'trash', 'untrash'];

    /** Every page state, as {@see $from} and {@see $to}. */
    public const array STATES = ['draft', 'in_review', 'approved', 'published', 'archived', 'trash'];

    /**
     * @param int         $pageId     the page
     * @param string      $transition one of {@see TRANSITIONS}
     * @param string      $from       the state before, one of {@see STATES}
     * @param string      $to         the state now, one of {@see STATES}
     * @param int|null    $actorId    the person who did it; null for the command line or `schedule:run`
     * @param string|null $comment    what the person wrote with the step (for example why a page was sent back)
     */
    public function __construct(
        public int $pageId,
        public string $transition,
        public string $from,
        public string $to,
        public ?int $actorId,
        public ?string $comment = null,
    ) {
        Guard::notNegative($pageId, 'Page id');
        self::oneOf($transition, self::TRANSITIONS, 'Page workflow step');
        self::oneOf($from, self::STATES, 'Page state before the step');
        self::oneOf($to, self::STATES, 'Page state after the step');
    }

    /**
     * True when this step put the page on the site.
     */
    public function published(): bool
    {
        return $this->to === 'published' && $this->from !== 'published';
    }

    /**
     * True when this step took the page off the site.
     */
    public function unpublished(): bool
    {
        return $this->from === 'published' && $this->to !== 'published';
    }

    /**
     * @param list<string> $allowed
     */
    private static function oneOf(string $value, array $allowed, string $field): void
    {
        if (!in_array($value, $allowed, true)) {
            throw new InvalidValueException(sprintf('%s is not valid ("%s"): use one of %s.', $field, $value, implode(', ', $allowed)));
        }
    }
}
