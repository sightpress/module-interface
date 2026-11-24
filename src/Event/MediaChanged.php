<?php

declare(strict_types=1);

namespace SightPress\Module\Event;

use SightPress\Module\Internal\Guard;

/**
 * Details of a media item changed. Part of the stable event list (1.0 API).
 */
final readonly class MediaChanged
{
    /** @var list<string> */
    public array $fields;

    /**
     * @param int          $mediaId   the media item
     * @param int|null     $changedBy the person who changed it
     * @param list<string> $fields    what changed: `alt`, `caption`, `transcript`, `captions`, `published` …
     */
    public function __construct(
        public int $mediaId,
        public ?int $changedBy,
        array $fields,
    ) {
        Guard::notNegative($mediaId, 'Media id');
        $this->fields = Guard::listOfStrings($fields, 'Changed media fields');
    }
}
