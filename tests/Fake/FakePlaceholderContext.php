<?php

declare(strict_types=1);

namespace SightPress\Module\Tests\Fake;

use SightPress\Module\Placeholder\PlaceholderContext;

final readonly class FakePlaceholderContext implements PlaceholderContext
{
    /**
     * @param non-empty-string $locale
     */
    public function __construct(private string $locale = 'en-GB', private ?int $viewerId = null)
    {
    }

    public function locale(): string
    {
        return $this->locale;
    }

    public function viewerId(): ?int
    {
        return $this->viewerId;
    }
}
