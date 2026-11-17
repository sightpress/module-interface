<?php

declare(strict_types=1);

namespace SightPress\Module\Assistant;

/**
 * What a provider's model can do besides text. Core offers a task only when the model can do what it needs (for
 * example alt text needs vision).
 */
final readonly class ProviderCapabilities
{
    /**
     * @param bool $vision    whether the model reads images ({@see AssistantImage})
     * @param bool $streaming whether answers can be streamed; only used when the provider implements
     *                        {@see StreamingAssistantProvider}
     */
    public function __construct(public bool $vision = false, public bool $streaming = false)
    {
    }
}
