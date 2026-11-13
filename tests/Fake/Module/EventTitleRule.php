<?php

declare(strict_types=1);

namespace SightPress\Module\Tests\Fake\Module;

use SightPress\Module\Block\A11yIssue;
use SightPress\Module\Block\A11yRule;
use SightPress\Module\Block\A11ySeverity;

/**
 * An event card's link text is its title, so an empty title gives a link without an accessible name.
 */
final class EventTitleRule implements A11yRule
{
    public function check(array $data): array
    {
        $title = $data['title'] ?? '';
        if (is_string($title) && trim($title) !== '') {
            return [];
        }

        return [new A11yIssue(
            'event_card.title_missing',
            'Give the event a title: it is the text of the link to the event.',
            A11ySeverity::Error,
            '/title',
            ['2.4.4', '4.1.2'],
        )];
    }
}
