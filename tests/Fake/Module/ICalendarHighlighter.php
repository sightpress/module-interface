<?php

declare(strict_types=1);

namespace SightPress\Module\Tests\Fake\Module;

use SightPress\Module\Block\CodeHighlighter;
use SightPress\Module\Block\CodeToken;
use SightPress\Module\Block\CodeTokenKind;

/**
 * Highlights iCalendar (`.ics`) lines: the property name, the colon, and the value.
 */
final readonly class ICalendarHighlighter implements CodeHighlighter
{
    public function highlight(string $code, string $language): iterable
    {
        $lines = preg_split('/(\R)/', $code, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
        foreach ($lines === false ? [] : $lines as $line) {
            if (preg_match('/^([A-Z-]+)(:)(.*)$/s', $line, $parts) === 1) {
                yield new CodeToken($parts[1], CodeTokenKind::Keyword);
                yield new CodeToken($parts[2], CodeTokenKind::Punctuation);
                if ($parts[3] !== '') {
                    yield new CodeToken($parts[3], CodeTokenKind::String);
                }

                continue;
            }
            yield new CodeToken($line);
        }
    }
}
