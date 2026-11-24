<?php

declare(strict_types=1);

namespace SightPress\Module\Event;

use SightPress\Module\Exception\InvalidValueException;
use SightPress\Module\Internal\Guard;

/**
 * A redirect (or a "gone" answer) was added or removed. Part of the stable event list (1.0 API).
 */
final readonly class RedirectChanged
{
    public const string ADDED = 'added';
    public const string REMOVED = 'removed';

    /** @var non-empty-string */
    public string $locale;

    /**
     * @param self::ADDED|self::REMOVED $change
     * @param string                    $locale  the language whose addresses it covers (BCP 47)
     * @param string                    $from    the old address, without the leading `/`
     * @param string|null               $to      where it sends people, without the leading `/`; null for "gone"
     * @param int|null                  $actorId the person who changed it
     */
    public function __construct(
        public string $change,
        string $locale,
        public string $from,
        public ?string $to,
        public ?int $actorId,
    ) {
        if ($change !== self::ADDED && $change !== self::REMOVED) {
            throw new InvalidValueException(sprintf('Redirect change is not valid ("%s"): use added or removed.', $change));
        }
        $this->locale = Guard::locale($locale, 'Redirect locale');
    }
}
