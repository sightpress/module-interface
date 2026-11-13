<?php

declare(strict_types=1);

namespace SightPress\Module\Tests\Fake;

use SightPress\Module\Block\BlockContext;

final readonly class FakeBlockContext implements BlockContext
{
    /**
     * @param non-empty-string $locale
     * @param array<string, non-empty-string> $media media id => URL
     */
    public function __construct(
        private string $locale = 'en-GB',
        private string $baseUrl = 'https://example.org',
        private array $media = [],
    ) {
    }

    public function locale(): string
    {
        return $this->locale;
    }

    public function absoluteUrl(string $path): string
    {
        return $this->baseUrl . '/' . ltrim($path, '/');
    }

    public function mediaUrl(string $mediaId, ?string $variant = null): ?string
    {
        return $this->media[$mediaId] ?? null;
    }
}
