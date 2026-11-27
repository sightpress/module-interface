<?php

declare(strict_types=1);

namespace SightPress\Module\Locale;

/**
 * How long a formatted date or time is, from "Friday 27 November 2026" (full) to "27/11/2026" (short), in the
 * format locale's own pattern.
 */
enum FormatStyle: string
{
    case Full = 'full';

    case Long = 'long';

    case Medium = 'medium';

    case Short = 'short';
}
