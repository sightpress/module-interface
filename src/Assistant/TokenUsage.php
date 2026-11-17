<?php

declare(strict_types=1);

namespace SightPress\Module\Assistant;

use SightPress\Module\Internal\Guard;

/**
 * Tokens a provider counted for one answer, as it reports them. Core counts them against the site's monthly
 * budget and records them in the audit log (never the text).
 */
final readonly class TokenUsage
{
    public int $inputTokens;

    public int $outputTokens;

    public function __construct(int $inputTokens = 0, int $outputTokens = 0)
    {
        $this->inputTokens = Guard::notNegative($inputTokens, 'Input tokens');
        $this->outputTokens = Guard::notNegative($outputTokens, 'Output tokens');
    }

    public function total(): int
    {
        return $this->inputTokens + $this->outputTokens;
    }

    public function plus(self $other): self
    {
        return new self($this->inputTokens + $other->inputTokens, $this->outputTokens + $other->outputTokens);
    }
}
