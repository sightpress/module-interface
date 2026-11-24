<?php

declare(strict_types=1);

namespace SightPress\Module\Block;

use SightPress\Module\Internal\Guard;

/**
 * An icon that blocks with icons can show, such as a brand logo in `social_links`. The SVG goes through the same
 * sanitiser as uploads; it is drawn with `currentColor`, hidden from screen readers, and the link's text always
 * names it.
 */
final readonly class BlockIcon
{
    /** @var non-empty-string */
    public string $name;

    /** @var non-empty-string */
    public string $label;

    /** @var non-empty-string */
    public string $svgFile;

    /** @var list<non-empty-string> */
    public array $hosts;

    public ?string $brandColour;

    /**
     * @param string       $name        lower-case letters, digits and `-`, for example `mastodon`
     * @param string       $label       the name people know it by, for example `Mastodon`
     * @param string       $svgFile     absolute path of the SVG file
     * @param list<string> $hosts       link hosts it stands for, for example `mastodon.social`; subdomains match too
     * @param string|null  $brandColour the brand's official colour as `#rrggbb`, used when an editor chooses brand
     *                                  colours and it has enough contrast
     */
    public function __construct(string $name, string $label, string $svgFile, array $hosts = [], ?string $brandColour = null)
    {
        $this->name = Guard::matches($name, '/^[a-z][a-z0-9-]*$/', 'Icon name', 'use lower-case letters, digits and "-", for example "mastodon"');
        $this->label = Guard::notBlank($label, sprintf('Label of icon "%s"', $name));
        $this->svgFile = Guard::matches($svgFile, '#^(?:/|[A-Za-z]:[\\\\/]).*\.svg$#', sprintf('SVG file of icon "%s"', $name), 'use an absolute path to an .svg file');
        $checked = [];
        foreach (Guard::listOfStrings($hosts, sprintf('Hosts of icon "%s"', $name)) as $host) {
            $checked[] = Guard::matches($host, '/^(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z]{2,}$/', sprintf('Host of icon "%s"', $name), 'use a lower-case host name such as "mastodon.social"');
        }
        Guard::unique($checked, sprintf('Hosts of icon "%s"', $name));
        $this->hosts = $checked;
        $this->brandColour = $brandColour === null ? null : Guard::matches($brandColour, '/^#[0-9a-f]{6}$/', sprintf('Brand colour of icon "%s"', $name), 'use a lower-case hex colour such as "#6364ff"');
    }
}
