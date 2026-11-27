<?php

declare(strict_types=1);

namespace SightPress\Module\Tests\Unit\Contract;

use DateTimeImmutable;
use DateTimeZone;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use SightPress\Module\Block\CodeLanguage;
use SightPress\Module\Block\CodeToken;
use SightPress\Module\Block\CodeTokenKind;
use SightPress\Module\Locale\FormatStyle;
use SightPress\Module\Locale\NameWidth;
use SightPress\Module\Money\Money;
use SightPress\Module\Placeholder\Placeholder;
use SightPress\Module\Placeholder\PlaceholderType;
use SightPress\Module\Privacy\ConsentPurpose;
use SightPress\Module\Privacy\CookieDeclaration;
use SightPress\Module\Privacy\PersonalData;
use SightPress\Module\Privacy\PersonalDataProvider;
use SightPress\Module\Privacy\ProcessorDeclaration;
use SightPress\Module\Privacy\StorageKind;
use SightPress\Module\Privacy\UserRef;
use SightPress\Module\Tests\Fake\FakeConsentManager;
use SightPress\Module\Tests\Fake\FakeLocaleFormats;
use SightPress\Module\Tests\Fake\FakeMoneyFormatter;
use SightPress\Module\Tests\Fake\FakePlaceholderContext;
use SightPress\Module\Tests\Fake\FakeSettingValues;
use SightPress\Module\Tests\Fake\FakeSiteLanguages;
use SightPress\Module\Tests\Fake\Module\Booking;
use SightPress\Module\Tests\Fake\Module\EventBookings;
use SightPress\Module\Tests\Fake\Module\EventBookingsProvider;
use SightPress\Module\Tests\Fake\Module\EventPlaceholders;
use SightPress\Module\Tests\Fake\Module\EventsModule;
use SightPress\Module\Tests\Fake\Module\EventSummary;
use SightPress\Module\Tests\Fake\Module\EventTicketingProcessors;
use SightPress\Module\Tests\Fake\Module\ICalendarHighlighter;
use SightPress\Module\Tests\Fake\Registrar\DuplicateRegistration;
use SightPress\Module\Tests\Fake\Registrar\InMemoryModuleRegistrar;
use SightPress\Module\Text\Message;

/**
 * Contract test for the 0.6 additions: a module declares its personal data, cookies, processors and consent
 * purposes, exports and erases a person's data through an in-memory provider, adds placeholders and a code
 * language, and formats dates, money and language labels through core's services.
 */
#[CoversNothing]
final class PrivacyAndFormatsContractTest extends TestCase
{
    private const string CANARY = 'canary.person@example.org';

    public function testModuleDeclaresItsPrivacyThroughTheRegistrar(): void
    {
        $registrar = new InMemoryModuleRegistrar();
        new EventsModule()->register($registrar);

        self::assertSame([EventBookingsProvider::class], $registrar->privacy->providers);
        self::assertSame([EventTicketingProcessors::class], $registrar->privacy->processorSources);
        self::assertSame(['acme_events.map'], array_keys($registrar->privacy->consentPurposes));

        $cookies = $registrar->privacy->cookies;
        self::assertSame(['acme_events_list_view', 'acme_maps_session'], array_keys($cookies));
        self::assertSame(StorageKind::LocalStorage, $cookies['acme_events_list_view']->storage);
        $waiting = array_filter($cookies, static fn (CookieDeclaration $cookie): bool => !$cookie->strictlyNecessary);
        foreach ($waiting as $cookie) {
            self::assertArrayHasKey((string) $cookie->consentPurpose, $registrar->privacy->consentPurposes, 'every optional cookie waits for a registered purpose');
        }

        self::assertSame(['acme_events.next_date', 'acme_events.my_bookings'], array_keys($registrar->placeholders->placeholders));
        self::assertTrue($registrar->placeholders->placeholders['acme_events.my_bookings']->personal);
        self::assertSame(['icalendar'], array_keys($registrar->blockTypes->codeLanguages));
    }

    public function testEveryMarkedFieldIsCoveredByAProvider(): void
    {
        $registrar = new InMemoryModuleRegistrar();
        new EventsModule()->register($registrar);
        $bookings = new EventBookings();
        $covered = array_map(
            static fn (string $provider): string => self::provider($provider, $bookings)->describe()->key,
            $registrar->privacy->providers,
        );

        $marked = [];
        foreach (new ReflectionClass(Booking::class)->getConstructor()?->getParameters() ?? [] as $parameter) {
            foreach ($parameter->getAttributes(PersonalData::class) as $attribute) {
                $marked[$parameter->getName()] = $attribute->newInstance()->dataSet;
            }
        }

        self::assertSame(['email' => 'acme_events.bookings', 'userId' => 'acme_events.bookings'], $marked);
        self::assertSame([], array_diff($marked, $covered));
    }

    public function testProviderExportsAndErasesOnlyThePersonsData(): void
    {
        $bookings = new EventBookings();
        $bookings->book(7, self::CANARY, 'fado', '2026-11-20T19:00:00Z', 'ticket-7.pdf');
        $bookings->book(0, 'CANARY.person@example.org', 'open-day', '2026-11-21T10:00:00Z');
        $bookings->book(8, 'someone.else@example.org', 'fado', '2026-11-20T19:05:00Z', 'ticket-8.pdf');
        $provider = new EventBookingsProvider($bookings);
        $user = new UserRef(7, self::CANARY);

        $set = $provider->describe();
        self::assertSame('acme_events.bookings', $set->key);
        self::assertSame(365, $set->retention->days);

        $exported = [...$provider->export($user)];
        self::assertSame(['fado', 'open-day'], array_column($exported, 'event'));
        self::assertNotFalse(json_encode($exported, JSON_THROW_ON_ERROR));
        self::assertStringNotContainsString('someone.else', json_encode($exported, JSON_THROW_ON_ERROR));

        $result = $provider->erase($user);
        self::assertSame([2, 0, 1], [$result->deleted, $result->pseudonymised, $result->files]);
        self::assertStringNotContainsStringIgnoringCase(self::CANARY, json_encode([$bookings->rows, $bookings->files], JSON_THROW_ON_ERROR));
        self::assertSame(['someone.else@example.org'], array_column(array_values($bookings->rows), 'email'));
        self::assertSame(['ticket-8.pdf'], array_keys($bookings->files));

        self::assertTrue($provider->erase($user)->isEmpty(), 'erasing again (after a backup restore) is safe');
        self::assertSame([], [...$provider->export($user)]);
    }

    public function testProcessorsAreDeclaredOnlyWhileConfigured(): void
    {
        self::assertSame([], [...new EventTicketingProcessors(new FakeSettingValues(['acme_events.ticketing_key' => null]))->processors()]);
        self::assertSame([], [...new EventTicketingProcessors(new FakeSettingValues(['acme_events.ticketing_key' => '']))->processors()]);

        $declared = [...new EventTicketingProcessors(new FakeSettingValues(['acme_events.ticketing_key' => 'k']))->processors()];
        self::assertCount(1, $declared);
        self::assertInstanceOf(ProcessorDeclaration::class, $declared[0]);
        self::assertSame(['api.acme-tickets.example'], $declared[0]->hosts);
        self::assertSame(['US'], array_values(array_diff($declared[0]->countries, ['GB'])), 'a transfer outside the UK shows up');
    }

    public function testConsentWaitsForTheVisitorAndHonoursGpc(): void
    {
        $consent = new FakeConsentManager();
        $request = new ServerRequest('GET', '/events');

        self::assertFalse($consent->allows($request, 'acme_events.map'), 'nothing is pre-ticked');
        self::assertTrue($consent->allows($request->withCookieParams(['consent' => 'acme_events.map']), 'acme_events.map'));
        self::assertFalse($consent->allows($request->withCookieParams(['consent' => 'acme_events.map'])->withHeader('Sec-GPC', '1'), 'acme_events.map'));

        $consent->record(7, 'acme_events.news_by_text', '2026-11-27');
        self::assertTrue($consent->hasConsented(7, 'acme_events.news_by_text'));
        $consent->withdraw(7, 'acme_events.news_by_text');
        $consent->withdraw(7, 'acme_events.news_by_text');
        self::assertFalse($consent->hasConsented(7, 'acme_events.news_by_text'));
        self::assertSame([], $consent->purposes());
    }

    public function testPlaceholdersGivePlainValuesAndPersonalOnesNeedAViewer(): void
    {
        $bookings = new EventBookings();
        $bookings->book(7, self::CANARY, 'fado', '2026-11-20T19:00:00Z');
        $resolver = new EventPlaceholders($bookings);

        self::assertInstanceOf(DateTimeImmutable::class, $resolver->resolve('acme_events.next_date', new FakePlaceholderContext()));
        self::assertSame('Casa do Fado', $resolver->resolve('acme_events.venue', new FakePlaceholderContext()));
        self::assertSame(4.5, $resolver->resolve('acme_events.rating', new FakePlaceholderContext()));
        self::assertNull($resolver->resolve('acme_events.my_bookings', new FakePlaceholderContext()), 'no viewer on a public page');
        self::assertSame(1, $resolver->resolve('acme_events.my_bookings', new FakePlaceholderContext('pt-PT', 7)));
        self::assertNull($resolver->resolve('acme_events.unknown', new FakePlaceholderContext()));
        self::assertSame('pt-PT', new FakePlaceholderContext('pt-PT')->locale());
        self::assertSame('number', PlaceholderType::Number->value);
    }

    public function testHighlighterTokensJoinToTheCode(): void
    {
        $code = "BEGIN:VEVENT\nSUMMARY:Fado <night>\n  folded line\nEND:";
        $tokens = [...new ICalendarHighlighter()->highlight($code, 'icalendar')];

        self::assertSame($code, implode('', array_map(static fn (CodeToken $token): string => $token->text, $tokens)));
        self::assertSame(CodeTokenKind::Keyword, $tokens[0]->kind);
        self::assertSame('Fado <night>', $tokens[6]->text, 'tokens are plain text: core escapes them');
        self::assertSame(['  folded line', null], [$tokens[8]->text, $tokens[8]->kind]);
        self::assertSame([], [...new ICalendarHighlighter()->highlight('', 'icalendar')]);
    }

    public function testModuleFormatsLikeCore(): void
    {
        $startsAt = new DateTimeImmutable('2026-12-04 19:30', new DateTimeZone('UTC'));
        $single = new EventSummary(new FakeLocaleFormats(), new FakeMoneyFormatter(), new FakeSiteLanguages(['en-GB']));
        $multi = new EventSummary(new FakeLocaleFormats(), new FakeMoneyFormatter(), new FakeSiteLanguages(['en-GB', 'pt-PT']));

        self::assertSame('Fado night, 4 December 2026, £12.50', $single->line('Fado night', $startsAt, new Money(1250, 'GBP'), 'en-GB'));
        self::assertSame('Fado night, 4 December 2026, 12,50 € (pt-PT)', $multi->line('Fado night', $startsAt, new Money(1250, 'EUR'), 'pt-PT'));

        $languages = new FakeSiteLanguages(['pt-PT', 'en-GB']);
        self::assertSame(['pt-PT', 'pt-PT'], [$languages->default(), $languages->locales()[0]]);

        $money = new FakeMoneyFormatter();
        self::assertSame(['¥1,250', 'USD 12.50'], [$money->format(new Money(1250, 'JPY')), $money->format(new Money(1250, 'USD'))]);

        $formats = new FakeLocaleFormats();
        self::assertSame(['en-GB', 'Europe/London', 'de-CH', 'en-GB'], [$formats->locale(), $formats->timeZone()->getName(), $formats->withLocale('de-CH')->locale(), $formats->withLocale('')->locale()]);
        self::assertSame(['Friday 4 December 2026', '4 Dec 2026', '04/12/2026'], [$formats->date($startsAt, FormatStyle::Full), $formats->date($startsAt, FormatStyle::Medium), $formats->date($startsAt, FormatStyle::Short)]);
        self::assertSame(['19:30', '19:30:00', '4 Dec 2026, 19:30'], [$formats->time($startsAt), $formats->time($startsAt, FormatStyle::Medium), $formats->dateTime($startsAt)]);
        self::assertSame(['November', 'Nov', 'N'], [$formats->monthName(11), $formats->monthName(11, NameWidth::Abbreviated), $formats->monthName(11, NameWidth::Narrow)]);
        self::assertSame(['Monday', 'Sunday', 1], [$formats->weekdayName(1), $formats->weekdayName(7), $formats->firstDayOfWeek()]);
        self::assertSame(['1,234', '1,234.50', '1,234.5', '25%', '12.5%'], [$formats->number(1234), $formats->number(1234.5), $formats->number(1234.5, 1), $formats->percent(0.25), $formats->percent(0.125, 1)]);
        self::assertSame(['2.5 KB', '2.5 MB'], [$formats->fileSize(2500), $formats->fileSize(2_500_000)]);
    }

    public function testRegistrarsRefuseDuplicates(): void
    {
        $registrar = new InMemoryModuleRegistrar();
        new EventsModule()->register($registrar);
        $label = new Message('Label');

        $again = [
            'provider' => static fn () => $registrar->privacy()->addProvider(EventBookingsProvider::class),
            'processors' => static fn () => $registrar->privacy()->addProcessors(EventTicketingProcessors::class),
            'cookie' => static fn () => $registrar->privacy()->addCookie(new CookieDeclaration('acme_maps_session', $label, null, true)),
            'consent purpose' => static fn () => $registrar->privacy()->addConsentPurpose(new ConsentPurpose('acme_events.map', $label, $label)),
            'placeholder' => static fn () => $registrar->placeholders()->add(new Placeholder('acme_events.next_date', $label, $label, PlaceholderType::Date, false, EventPlaceholders::class)),
            'code language alias' => static fn () => $registrar->blockTypes()->addCodeLanguage(new CodeLanguage('ics', 'ICS')),
            'code language id' => static fn () => $registrar->blockTypes()->addCodeLanguage(new CodeLanguage('calendar', 'Calendar', ['icalendar'])),
        ];
        foreach ($again as $what => $register) {
            try {
                $register();
                self::fail(sprintf('A second %s was accepted.', $what));
            } catch (DuplicateRegistration) {
                $this->addToAssertionCount(1);
            }
        }

        $registrar->blockTypes()->addCodeLanguage(new CodeLanguage('twig', 'Twig'));
        self::assertSame(['icalendar', 'twig'], array_keys($registrar->blockTypes->codeLanguages));
    }

    /**
     * @param class-string<PersonalDataProvider> $class
     */
    private static function provider(string $class, EventBookings $bookings): PersonalDataProvider
    {
        return new $class($bookings);
    }
}
