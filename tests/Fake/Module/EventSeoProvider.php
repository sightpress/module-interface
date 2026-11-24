<?php

declare(strict_types=1);

namespace SightPress\Module\Tests\Fake\Module;

use DateTimeImmutable;
use SightPress\Module\Seo\SeoEntry;
use SightPress\Module\Seo\SeoProvider;

/**
 * Lists the fake module's public events for the sitemap and `llms.txt`, with `Event` JSON-LD, as a module author
 * would. The members-only workshop is left out.
 */
final class EventSeoProvider implements SeoProvider
{
    public function entries(string $locale): iterable
    {
        $alternates = ['en-GB' => '/events/fado', 'pt-PT' => '/pt-pt/eventos/fado'];
        if (!isset($alternates[$locale])) {
            return;
        }

        yield self::entry($locale, $alternates[$locale], $locale === 'pt-PT' ? 'Noite de fado' : 'Fado night', $alternates);
    }

    /**
     * @param non-empty-string      $path
     * @param array<string, string> $alternates
     */
    public static function entry(string $locale, string $path, string $title, array $alternates): SeoEntry
    {
        return new SeoEntry(
            $path,
            $locale,
            $title,
            new DateTimeImmutable('2026-11-01T10:00:00+00:00'),
            'Live fado music and poetry.',
            $path . '.md',
            $alternates,
            [['@type' => 'Event', 'name' => $title, 'startDate' => '2026-12-01T20:00:00Z', 'inLanguage' => $locale]],
        );
    }
}
