<?php

declare(strict_types=1);

namespace SightPress\Module\Event;

use SightPress\Module\Internal\Guard;

/**
 * The AI assistant answered (or failed to answer) a request. Carries no prompt or answer text. Part of the stable
 * event list (1.0 API).
 */
final readonly class AssistantUsed
{
    /**
     * @param int|null $actorId      the person who asked
     * @param string   $task         the task id, for example `alt_text`
     * @param string   $provider     the provider id
     * @param string   $model        the model
     * @param int      $inputTokens  tokens sent
     * @param int      $outputTokens tokens received
     * @param string   $outcome      `ok` or the failure, for example `timeout`
     */
    public function __construct(
        public ?int $actorId,
        public string $task,
        public string $provider,
        public string $model,
        public int $inputTokens,
        public int $outputTokens,
        public string $outcome,
    ) {
        Guard::notNegative($inputTokens, 'Input tokens');
        Guard::notNegative($outputTokens, 'Output tokens');
    }
}
