<?php

declare(strict_types=1);

namespace SightPress\Module\Tests\Fake;

use SightPress\Module\Assistant\AssistantContext;

final readonly class FakeAssistantContext implements AssistantContext
{
    /**
     * @param non-empty-string      $locale
     * @param list<string>          $permissions
     * @param non-empty-string|null $screen
     */
    public function __construct(
        private string $locale = 'en-GB',
        private array $permissions = [],
        private ?string $screen = null,
    ) {
    }

    public function locale(): string
    {
        return $this->locale;
    }

    public function can(string $permission): bool
    {
        return in_array($permission, $this->permissions, true);
    }

    public function screen(): ?string
    {
        return $this->screen;
    }
}
