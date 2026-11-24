<?php

declare(strict_types=1);

namespace SightPress\Module\Event;

use SightPress\Module\Internal\Guard;

/**
 * A media item and its files were deleted. Part of the stable event list (1.0 API).
 */
final readonly class MediaDeleted
{
    /**
     * @param int      $mediaId      the media item that was deleted
     * @param string   $originalName its file name when it was uploaded, as it can no longer be looked up
     * @param int|null $deletedBy    the person who deleted it
     */
    public function __construct(
        public int $mediaId,
        public string $originalName,
        public ?int $deletedBy,
    ) {
        Guard::notNegative($mediaId, 'Media id');
    }
}
