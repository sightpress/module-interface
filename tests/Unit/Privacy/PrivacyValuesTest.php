<?php

declare(strict_types=1);

namespace SightPress\Module\Tests\Unit\Privacy;

use Closure;
use LogicException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use SightPress\Module\Exception\InvalidValueException;
use SightPress\Module\Internal\Guard;
use SightPress\Module\Privacy\ConsentPurpose;
use SightPress\Module\Privacy\CookieDeclaration;
use SightPress\Module\Privacy\ErasureResult;
use SightPress\Module\Privacy\ErasureRule;
use SightPress\Module\Privacy\LawfulBasis;
use SightPress\Module\Privacy\PersonalData;
use SightPress\Module\Privacy\PersonalDataCategory;
use SightPress\Module\Privacy\PersonalDataSet;
use SightPress\Module\Privacy\ProcessorDeclaration;
use SightPress\Module\Privacy\Retention;
use SightPress\Module\Privacy\StorageKind;
use SightPress\Module\Privacy\UserRef;
use SightPress\Module\Sms\PhoneNumber;
use SightPress\Module\Text\Message;

#[CoversClass(UserRef::class)]
#[CoversClass(Retention::class)]
#[CoversClass(PersonalDataSet::class)]
#[CoversClass(ErasureResult::class)]
#[CoversClass(PersonalData::class)]
#[CoversClass(CookieDeclaration::class)]
#[CoversClass(ProcessorDeclaration::class)]
#[CoversClass(ConsentPurpose::class)]
#[UsesClass(Guard::class)]
#[UsesClass(Message::class)]
#[UsesClass(PhoneNumber::class)]
final class PrivacyValuesTest extends TestCase
{
    private const string CANARY = 'canary.person@example.org';

    public function testUserRefKeepsItsAddressAndNumberOutOfDumpsAndSerialisation(): void
    {
        $user = new UserRef(7, self::CANARY, new PhoneNumber('+447700900123'));

        self::assertSame([7, self::CANARY, '+447700900123'], [$user->userId, $user->email, $user->phone?->e164]);
        $dump = print_r($user, true);
        self::assertStringNotContainsString(self::CANARY, $dump);
        self::assertStringNotContainsString('900123', $dump);
        self::assertSame(['userId' => 7, 'email' => '•••', 'phone' => '+44•••••••123'], $user->__debugInfo());
        self::assertNull(new UserRef(8, self::CANARY)->__debugInfo()['phone']);

        $this->expectException(LogicException::class);
        serialize($user);
    }

    public function testUserRefRefusesABadAddressWithoutRepeatingIt(): void
    {
        try {
            new UserRef(7, 'not an address');
            self::fail('No exception.');
        } catch (InvalidValueException $exception) {
            self::assertStringNotContainsString('not an address', $exception->getMessage());
        }

        $this->expectException(InvalidValueException::class);
        new UserRef(-1, self::CANARY);
    }

    public function testRetention(): void
    {
        self::assertNull(Retention::whileAccountExists()->days);
        $retention = Retention::days(90, 'acme_events.retention_days');
        self::assertSame([90, 'acme_events.retention_days'], [$retention->days, $retention->setting]);
        self::assertNull(new Retention(30)->setting);

        $this->expectException(InvalidValueException::class);
        Retention::days(0);
    }

    public function testRetentionSettingIsADottedName(): void
    {
        $this->expectException(InvalidValueException::class);
        Retention::days(30, 'Retention Days');
    }

    public function testPersonalDataSet(): void
    {
        $set = new PersonalDataSet(
            'acme_events.bookings',
            new Message('Event bookings'),
            new Message('To keep your place.'),
            [PersonalDataCategory::Contact, PersonalDataCategory::Usage],
            LawfulBasis::Contract,
            Retention::days(365),
        );

        self::assertSame('acme_events.bookings', $set->key);
        self::assertSame([PersonalDataCategory::Contact, PersonalDataCategory::Usage], $set->categories);
        self::assertSame(ErasureRule::Delete, $set->erasure);
        self::assertNull($set->notes);

        $kept = new PersonalDataSet('acme.invoices', new Message('Invoices'), new Message('Tax records.'), [PersonalDataCategory::Identity], LawfulBasis::LegalObligation, Retention::days(2190), ErasureRule::Keep, new Message('Kept for six years by law.'));
        self::assertSame(ErasureRule::Keep, $kept->erasure);
    }

    /**
     * @return iterable<string, array{Closure(): mixed, string}>
     */
    public static function invalidValues(): iterable
    {
        $label = new Message('Label');
        /** @phpstan-ignore argument.type (testing the run-time check) */
        $set = static fn (string $key = 'acme.data', array $categories = [PersonalDataCategory::Contact], ErasureRule $erasure = ErasureRule::Delete): PersonalDataSet => new PersonalDataSet($key, $label, $label, $categories, LawfulBasis::Consent, Retention::whileAccountExists(), $erasure);

        yield 'data set key' => [static fn (): mixed => $set('Acme Data'), 'Personal data set key'];
        yield 'no category' => [static fn (): mixed => $set(categories: []), 'at least one category'];
        yield 'duplicate category' => [static fn (): mixed => $set(categories: [PersonalDataCategory::Contact, PersonalDataCategory::Contact]), 'duplicates'];
        yield 'not a category' => [static fn (): mixed => $set(categories: ['contact']), 'must be an instance'];
        yield 'kept without notes' => [static fn (): mixed => $set(erasure: ErasureRule::Keep), 'say why'];
        yield 'negative erasure count' => [static fn (): mixed => new ErasureResult(-1), 'must not be negative'];
        yield 'attribute data set' => [static fn (): mixed => new PersonalData('Bookings'), 'Personal data set key'];
        yield 'cookie name' => [static fn (): mixed => new CookieDeclaration('my cookie', $label, null, true), 'Cookie name'];
        yield 'cookie lifetime' => [static fn (): mixed => new CookieDeclaration('c', $label, 0, true), 'at least 1 second'];
        yield 'necessary cookie with consent' => [static fn (): mixed => new CookieDeclaration('c', $label, null, true, 'acme.maps'), 'strictly necessary'];
        yield 'optional cookie without consent' => [static fn (): mixed => new CookieDeclaration('c', $label, null, false), 'name the consent purpose'];
        yield 'cookie consent key' => [static fn (): mixed => new CookieDeclaration('c', $label, null, false, 'Maps'), 'Consent purpose key'];
        yield 'consent purpose key' => [static fn (): mixed => new ConsentPurpose('Maps', $label, $label), 'Consent purpose key'];
        yield 'processor id' => [static fn (): mixed => new ProcessorDeclaration('Twilio', 'Twilio Inc.', $label, ['US']), 'Processor id'];
        yield 'processor name' => [static fn (): mixed => new ProcessorDeclaration('acme.sms', ' ', $label, ['US']), 'Processor name'];
        yield 'no country' => [static fn (): mixed => new ProcessorDeclaration('acme.sms', 'Acme', $label, []), 'at least one country'];
        yield 'bad country' => [static fn (): mixed => new ProcessorDeclaration('acme.sms', 'Acme', $label, ['USA']), 'Country'];
        yield 'duplicate country' => [static fn (): mixed => new ProcessorDeclaration('acme.sms', 'Acme', $label, ['us', 'US']), 'duplicates'];
        yield 'host with scheme' => [static fn (): mixed => new ProcessorDeclaration('acme.sms', 'Acme', $label, ['US'], ['https://api.acme.example']), 'Processor host'];
        yield 'duplicate host' => [static fn (): mixed => new ProcessorDeclaration('acme.sms', 'Acme', $label, ['US'], ['api.acme.example', 'API.acme.example']), 'duplicates'];
        yield 'policy over http' => [static fn (): mixed => new ProcessorDeclaration('acme.sms', 'Acme', $label, ['US'], [], 'http://acme.example/privacy'), 'https address'];
    }

    /**
     * @param Closure(): mixed $make
     */
    #[DataProvider('invalidValues')]
    public function testRefusesInvalidValues(Closure $make, string $message): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage($message);
        $make();
    }

    public function testErasureResult(): void
    {
        self::assertTrue(ErasureResult::nothing()->isEmpty());
        $result = new ErasureResult(deleted: 2, pseudonymised: 3, files: 1);
        self::assertSame([2, 3, 1], [$result->deleted, $result->pseudonymised, $result->files]);
        self::assertFalse($result->isEmpty());
        self::assertFalse(new ErasureResult(pseudonymised: 1)->isEmpty());
        self::assertFalse(new ErasureResult(files: 1)->isEmpty());
    }

    public function testPersonalDataAttribute(): void
    {
        $attribute = new PersonalData('acme_events.bookings', PersonalDataCategory::Contact);
        self::assertSame(['acme_events.bookings', PersonalDataCategory::Contact], [$attribute->dataSet, $attribute->category]);
        self::assertNull(new PersonalData('acme_events.bookings')->category);
    }

    public function testCookieDeclarations(): void
    {
        $session = new CookieDeclaration('__Host-acme_session', new Message('Keeps you signed in.'), null, true);
        self::assertSame(['__Host-acme_session', null, true, null, StorageKind::Cookie], [$session->name, $session->lifetime, $session->strictlyNecessary, $session->consentPurpose, $session->storage]);

        $map = new CookieDeclaration('acme_map', new Message('Map tiles.'), 86_400, false, 'acme.maps', StorageKind::LocalStorage);
        self::assertSame([86_400, 'acme.maps', StorageKind::LocalStorage], [$map->lifetime, $map->consentPurpose, $map->storage]);
    }

    public function testProcessorDeclaration(): void
    {
        $processor = new ProcessorDeclaration(
            'acme_sms.twilio',
            'Twilio Inc.',
            new Message('Mobile numbers and message text.'),
            ['us', 'IE'],
            ['API.twilio.com', '*.twilio.com'],
            'https://www.twilio.com/legal/privacy',
            new Message('UK IDTA and EU standard contractual clauses.'),
        );

        self::assertSame(['US', 'IE'], $processor->countries);
        self::assertSame(['api.twilio.com', '*.twilio.com'], $processor->hosts);
        self::assertSame('https://www.twilio.com/legal/privacy', $processor->privacyPolicyUrl);
        self::assertNotNull($processor->safeguards);

        $mailer = new ProcessorDeclaration('core.mailer', 'Postmark', new Message('Email addresses.'), ['US']);
        self::assertSame([[], null, null], [$mailer->hosts, $mailer->privacyPolicyUrl, $mailer->safeguards]);
    }

    public function testConsentPurpose(): void
    {
        $purpose = new ConsentPurpose('acme.maps', new Message('Maps'), new Message('Shows maps from Acme.'));
        self::assertSame('acme.maps', $purpose->key);
        self::assertSame('Maps', $purpose->label->text);
    }
}
