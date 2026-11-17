<?php

declare(strict_types=1);

namespace SightPress\Module\Tests\Fake\Module;

use DateTimeImmutable;
use SightPress\Module\Search\SearchDocument;
use SightPress\Module\Search\SearchDocumentProvider;
use SightPress\Module\Search\SearchVisibility;

/**
 * Makes the fake module's published events searchable, as a module author would.
 */
final class EventSearchProvider implements SearchDocumentProvider
{
    /** @var array<string, array<string, array{path: non-empty-string, title: non-empty-string, body: string, members: bool}>> ref => locale => event */
    private const array EVENTS = [
        'fado' => [
            'en-GB' => ['path' => '/events/fado', 'title' => 'Fado night', 'body' => 'Live fado music and poetry.', 'members' => false],
            'pt-PT' => ['path' => '/pt-pt/eventos/fado', 'title' => 'Noite de fado', 'body' => 'Fado ao vivo e poesia.', 'members' => false],
        ],
        'workshop' => [
            'en-GB' => ['path' => '/events/workshop', 'title' => 'Screen reader workshop', 'body' => 'Members practise with screen readers.', 'members' => true],
        ],
    ];

    public function source(): string
    {
        return 'acme.events';
    }

    public function documents(string $locale): iterable
    {
        foreach (array_keys(self::EVENTS) as $ref) {
            $document = $this->document($ref, $locale);
            if ($document !== null) {
                yield $document;
            }
        }
    }

    public function document(string $ref, string $locale): ?SearchDocument
    {
        $event = self::EVENTS[$ref][$locale] ?? null;
        if ($event === null) {
            return null;
        }

        return new SearchDocument(
            $this->source(),
            $ref,
            $locale,
            $event['path'],
            $event['title'],
            new DateTimeImmutable('2026-11-01T10:00:00+00:00'),
            body: $event['body'],
            breadcrumb: ['Events'],
            visibility: $event['members'] ? SearchVisibility::Members : SearchVisibility::Public,
        );
    }
}
