<?php

declare(strict_types=1);

namespace SightPress\Module\Block;

/**
 * How serious an accessibility issue found in block data is.
 */
enum A11ySeverity: string
{
    /** Breaks WCAG 2.2 AA. The page can't be published until it is fixed. */
    case Error = 'error';

    /** Makes the content harder to use but doesn't break WCAG 2.2 AA. Shown to the editor; doesn't block. */
    case Warning = 'warning';
}
