<?php

declare(strict_types=1);

namespace SightPress\Module\Tests\Unit\Site;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use SightPress\Module\Exception\InvalidValueException;
use SightPress\Module\Internal\Guard;
use SightPress\Module\Site\SiteInfo;

#[CoversClass(SiteInfo::class)]
#[UsesClass(Guard::class)]
final class SiteInfoTest extends TestCase
{
    public function testSite(): void
    {
        $site = new SiteInfo('0b8e3c1e-6a3c-4f1e-9d2a-1f2e3d4c5b6a', 'demo', 'Demo', 'en-GB', ['en-GB', 'pt-PT', 'he']);

        self::assertSame('0b8e3c1e-6a3c-4f1e-9d2a-1f2e3d4c5b6a', $site->id);
        self::assertSame('demo', $site->slug);
        self::assertSame('Demo', $site->name);
        self::assertSame('en-GB', $site->defaultLocale);
        self::assertSame(['en-GB', 'pt-PT', 'he'], $site->locales);
    }

    public function testLocalesIncludeTheDefault(): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('The site\'s locales must include its default locale "en-GB".');

        new SiteInfo('id', 'demo', 'Demo', 'en-GB', ['pt-PT']);
    }

    public function testLocalesAreUnique(): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('Site locales has duplicates: en-GB.');

        new SiteInfo('id', 'demo', 'Demo', 'en-GB', ['en-GB', 'en-GB']);
    }
}
