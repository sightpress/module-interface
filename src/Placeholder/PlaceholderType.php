<?php

declare(strict_types=1);

namespace SightPress\Module\Placeholder;

/**
 * The kind of value a placeholder gives, so core can format and escape it for where it goes.
 */
enum PlaceholderType: string
{
    /** Plain text. */
    case Text = 'text';

    /** A number, formatted for the content's locale. */
    case Number = 'number';

    /** A day, formatted for the content's locale. */
    case Date = 'date';

    /** A day and time, formatted for the content's locale and the viewer's time zone. */
    case DateTime = 'date_time';

    /** A web address (http or https). */
    case Url = 'url';

    /** An email address. */
    case Email = 'email';

    /** A phone number, in E.164 or as written. */
    case Phone = 'phone';
}
