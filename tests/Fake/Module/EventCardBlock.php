<?php

declare(strict_types=1);

namespace SightPress\Module\Tests\Fake\Module;

use SightPress\Module\Block\BlockContext;
use SightPress\Module\Block\BlockOption;
use SightPress\Module\Block\BlockStyles;
use SightPress\Module\Block\BlockVariant;
use SightPress\Module\Block\StyledBlockType;

final class EventCardBlock implements StyledBlockType
{
    public function styles(): BlockStyles
    {
        return new BlockStyles(
            [new BlockVariant('plain', 'Plain'), new BlockVariant('card', 'Card')],
            [BlockOption::Align, BlockOption::Background, BlockOption::Spacing],
        );
    }

    public function name(): string
    {
        return 'acme.event_card';
    }

    public function label(): string
    {
        return 'Event card';
    }

    public function version(): int
    {
        return 2;
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'required' => ['title', 'slug', 'startsAt'],
            'properties' => [
                'title' => ['type' => 'string', 'minLength' => 1],
                'slug' => ['type' => 'string'],
                'startsAt' => ['type' => 'string', 'format' => 'date-time'],
            ],
        ];
    }

    public function upgradeData(int $fromVersion, array $data): array
    {
        // Version 1 called the start date "date".
        if ($fromVersion === 1 && array_key_exists('date', $data)) {
            $data['startsAt'] = $data['date'];
            unset($data['date']);
        }

        return $data;
    }

    public function render(array $data, BlockContext $context): string
    {
        return sprintf(
            '<article><h2><a href="%s">%s</a></h2><p><time datetime="%3$s">%3$s</time></p></article>',
            htmlspecialchars($context->absoluteUrl('/events/' . self::text($data, 'slug')), ENT_QUOTES),
            htmlspecialchars(self::text($data, 'title'), ENT_QUOTES),
            htmlspecialchars(self::text($data, 'startsAt'), ENT_QUOTES),
        );
    }

    public function renderMarkdown(array $data, BlockContext $context): string
    {
        return sprintf(
            "## [%s](%s)\n\nStarts at %s.\n",
            self::text($data, 'title'),
            $context->absoluteUrl('/events/' . self::text($data, 'slug')),
            self::text($data, 'startsAt'),
        );
    }

    public function structuredData(array $data, BlockContext $context): ?array
    {
        // A card without a title says nothing useful about the event.
        if (self::text($data, 'title') === '') {
            return null;
        }

        return [
            '@type' => 'Event',
            'name' => self::text($data, 'title'),
            'startDate' => self::text($data, 'startsAt'),
            'url' => $context->absoluteUrl('/events/' . self::text($data, 'slug')),
            'inLanguage' => $context->locale(),
        ];
    }

    public function a11yRules(): array
    {
        return [new EventTitleRule()];
    }

    /**
     * @param array<string, mixed> $data
     */
    private static function text(array $data, string $key): string
    {
        $value = $data[$key] ?? '';

        return is_string($value) ? $value : '';
    }
}
