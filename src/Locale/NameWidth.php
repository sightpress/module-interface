<?php

declare(strict_types=1);

namespace SightPress\Module\Locale;

/**
 * How long a month or weekday name is: "November", "Nov" or "N".
 */
enum NameWidth: string
{
    case Wide = 'wide';

    case Abbreviated = 'abbreviated';

    case Narrow = 'narrow';
}
