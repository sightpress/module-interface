<?php

declare(strict_types=1);

namespace SightPress\Module\Assistant;

/**
 * Who a message in an {@see AssistantRequest} comes from.
 */
enum AssistantRole: string
{
    /** Instructions from the task, sent before the conversation. */
    case System = 'system';

    /** The person's request and the context they chose, written by the task. */
    case User = 'user';

    /** An earlier answer of the model, for follow-up requests. */
    case Assistant = 'assistant';
}
