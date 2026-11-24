<?php

declare(strict_types=1);

namespace SightPress\Module\Event;

use SightPress\Module\Exception\InvalidValueException;
use SightPress\Module\Internal\Guard;

/**
 * A file was added to the media library. Part of the stable event list (1.0 API).
 */
final readonly class MediaUploaded
{
    /** Every kind of media file, as {@see $kind}. */
    public const array KINDS = ['image', 'video', 'audio', 'document'];

    /**
     * @param int      $mediaId    the new media item
     * @param string   $key        where the file is stored, relative to the site's storage
     * @param string   $kind       one of {@see KINDS}
     * @param int|null $uploadedBy the person who uploaded it; null for the command line
     */
    public function __construct(
        public int $mediaId,
        public string $key,
        public string $kind,
        public ?int $uploadedBy,
    ) {
        Guard::notNegative($mediaId, 'Media id');
        if (!in_array($kind, self::KINDS, true)) {
            throw new InvalidValueException(sprintf('Media kind is not valid ("%s"): use one of %s.', $kind, implode(', ', self::KINDS)));
        }
    }
}
