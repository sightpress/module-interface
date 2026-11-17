<?php

declare(strict_types=1);

namespace SightPress\Module\Assistant;

use SightPress\Module\Exception\InvalidValueException;

/**
 * An image sent with a message, for providers and models that can see images (alt text suggestions).
 */
final readonly class AssistantImage
{
    /** Image formats every vision provider accepts. */
    public const array MEDIA_TYPES = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

    /** @var non-empty-string */
    public string $data;

    /**
     * @param string $mediaType one of {@see MEDIA_TYPES}
     * @param string $data      the image file's bytes (not Base64); providers encode them as they need
     */
    public function __construct(public string $mediaType, string $data)
    {
        if (!in_array($mediaType, self::MEDIA_TYPES, true)) {
            throw new InvalidValueException(sprintf(
                'Image type "%s" is not supported: use one of %s.',
                $mediaType,
                implode(', ', self::MEDIA_TYPES),
            ));
        }
        if ($data === '') {
            throw new InvalidValueException('Image data must not be empty.');
        }
        $this->data = $data;
    }
}
