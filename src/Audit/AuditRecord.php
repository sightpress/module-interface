<?php

declare(strict_types=1);

namespace SightPress\Module\Audit;

use SightPress\Module\Exception\InvalidValueException;
use SightPress\Module\Internal\Guard;

/**
 * One line of the site's audit log, written by a module through {@see AuditTrail}.
 *
 * Never put secrets or full personal data (email addresses, phone numbers) in the summary or details: the audit log
 * is shown to admins and kept for a long time.
 */
final readonly class AuditRecord
{
    /** @var non-empty-string */
    public string $action;

    /** @var non-empty-string */
    public string $summary;

    /** @var array<string, scalar|list<scalar>|null> */
    public array $details;

    /**
     * @param string                                  $action      what happened, starting with the module id, for
     *                                                             example `acme.events.event_published`
     * @param string                                  $summary     one plain sentence, for example
     *                                                             `Published the event “Open day”.`
     * @param int|null                                $actorId     who did it; null for the command line or
     *                                                             `schedule:run`
     * @param string|null                             $subjectType the kind of thing it was done to, for example
     *                                                             `event`
     * @param string|int|null                         $subjectId   its id
     * @param array<string, scalar|list<scalar>|null> $details     extra values for the log's details column
     */
    public function __construct(
        string $action,
        string $summary,
        public ?int $actorId = null,
        public ?string $subjectType = null,
        public string|int|null $subjectId = null,
        array $details = [],
    ) {
        $this->action = Guard::matches($action, Guard::DOTTED_NAME, 'Audit action', 'use lower-case words joined by dots, starting with the module id');
        $this->summary = Guard::notBlank($summary, 'Audit summary');
        if ($subjectType !== null) {
            Guard::matches($subjectType, '/^[a-z][a-z0-9_]*$/', 'Audit subject type', 'use a lower-case word such as "event"');
        }
        foreach ($details as $key => $value) {
            if (!is_string($key) || !self::isDetail($value)) {
                throw new InvalidValueException(sprintf('Details of audit action "%s" must map names to text, numbers, true/false, null or lists of those.', $action));
            }
        }
        /** @var array<string, scalar|list<scalar>|null> $details */
        $this->details = $details;
    }

    private static function isDetail(mixed $value): bool
    {
        if ($value === null || is_scalar($value)) {
            return true;
        }

        return is_array($value) && array_is_list($value) && array_filter($value, static fn (mixed $item): bool => !is_scalar($item)) === [];
    }
}
