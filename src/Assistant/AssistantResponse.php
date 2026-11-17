<?php

declare(strict_types=1);

namespace SightPress\Module\Assistant;

use SightPress\Module\Internal\Guard;

/**
 * A model's answer, as the provider got it. It is untrusted: the task's
 * {@see AssistantTask::validateOutput()} checks it before anyone sees it.
 */
final readonly class AssistantResponse
{
    /** @var non-empty-string */
    public string $model;

    /**
     * @param string     $text      the answer text (may be empty when the model said nothing)
     * @param string     $model     the model that answered, as the provider names it
     * @param TokenUsage $usage     tokens used
     * @param bool       $truncated true when the answer stopped at the output token limit
     */
    public function __construct(
        public string $text,
        string $model,
        public TokenUsage $usage = new TokenUsage(),
        public bool $truncated = false,
    ) {
        $this->model = Guard::notBlank($model, 'Model of the assistant response');
    }
}
