<?php

declare(strict_types=1);

namespace SightPress\Module\Tests\Unit\Contract;

use DateTimeImmutable;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use SightPress\Module\Access\Viewer;
use SightPress\Module\Block\BlockOption;
use SightPress\Module\Event\PageTransitioned;
use SightPress\Module\Exception\SmsException;
use SightPress\Module\Http\AdminScreen;
use SightPress\Module\Schedule\ScheduleRun;
use SightPress\Module\Site\SiteInfo;
use SightPress\Module\Sms\PhoneLineType;
use SightPress\Module\Sms\PhoneNumber;
use SightPress\Module\Sms\SmsConnection;
use SightPress\Module\Sms\SmsDeliveryStatus;
use SightPress\Module\Sms\SmsFailure;
use SightPress\Module\Sms\SmsMessage;
use SightPress\Module\Sms\SmsOptChange;
use SightPress\Module\Sms\SmsPurpose;
use SightPress\Module\Sms\SmsReceipt;
use SightPress\Module\Tests\Fake\FakeAuditTrail;
use SightPress\Module\Tests\Fake\FakeScreens;
use SightPress\Module\Tests\Fake\FakeSettingValues;
use SightPress\Module\Tests\Fake\Labs\TestSmsProvider;
use SightPress\Module\Tests\Fake\Module\EventAdminHandler;
use SightPress\Module\Tests\Fake\Module\EventCardBlock;
use SightPress\Module\Tests\Fake\Module\EventReminderJob;
use SightPress\Module\Tests\Fake\Module\EventSeoProvider;
use SightPress\Module\Text\Message;

/**
 * Contract test for the 0.5 additions: the example "events" feature is written only against this package (an
 * admin screen with access, settings and audit; SEO entries; a scheduled job; a styled block; listening to a core
 * event), and an SMS adapter can be written the same way.
 */
#[CoversNothing]
final class ModuleFeatureContractTest extends TestCase
{
    public function testAdminScreenUsesOnlyContractServices(): void
    {
        $screens = new FakeScreens();
        $audit = new FakeAuditTrail();
        $handler = new EventAdminHandler($screens, new FakeSettingValues(['events.page_size' => 20]), $audit, new SiteInfo('id', 'demo', 'Demo', 'en-GB', ['en-GB']));
        $viewer = new Viewer(2, ['admin.access', 'events.manage']);
        $request = new ServerRequest('GET', '/admin/events')->withAttribute(Viewer::class, $viewer);

        self::assertSame(200, $handler->handle($request)->getStatusCode());
        self::assertInstanceOf(AdminScreen::class, $screens->rendered);
        self::assertSame(['events' => ['fado' => 'Fado night'], 'page_size' => 20, 'can_delete' => true, 'site_name' => 'Demo'], $screens->rendered->context);

        $response = $handler->handle(new ServerRequest('POST', '/admin/events/fado/delete')->withAttribute(Viewer::class, $viewer));
        self::assertSame(303, $response->getStatusCode());
        self::assertSame('/admin/events', $response->getHeaderLine('Location'));
        self::assertInstanceOf(Message::class, $screens->notice);
        self::assertSame(['title' => 'Fado night'], $screens->notice->parameters);
        self::assertSame('acme.events.event_deleted', $audit->records[0]->action);
        self::assertSame(2, $audit->records[0]->actorId);
    }

    public function testSeoEntriesCarryStructuredDataPerLanguage(): void
    {
        $provider = new EventSeoProvider();

        $entries = [...$provider->entries('pt-PT')];
        self::assertCount(1, $entries);
        self::assertSame('/pt-pt/eventos/fado', $entries[0]->path);
        self::assertSame('/pt-pt/eventos/fado.md', $entries[0]->markdownPath);
        self::assertSame('Event', $entries[0]->structuredData[0]['@type']);
        self::assertSame(['en-GB', 'pt-PT'], array_keys($entries[0]->alternates));
        self::assertSame([], [...$provider->entries('he')]);
    }

    public function testScheduledJobRunsOnceAndReports(): void
    {
        $job = new EventReminderJob();

        $early = new ScheduleRun(new DateTimeImmutable('2026-11-29T12:00:00Z'));
        $job->run($early);
        self::assertSame([], $early->lines());

        $due = new ScheduleRun(new DateTimeImmutable('2026-12-01T12:00:00Z'));
        $job->run($due);
        self::assertSame(['Sent 2 event reminders.'], $due->lines());

        $again = new ScheduleRun(new DateTimeImmutable('2026-12-01T12:01:00Z'));
        $job->run($again);
        self::assertSame([], $again->lines(), 'never twice');

        $single = new EventReminderJob();
        $first = new ScheduleRun(new DateTimeImmutable('2026-11-30T21:00:00Z'));
        $single->run($first);
        self::assertSame(['Sent 1 event reminder.'], $first->lines());
    }

    public function testStyledBlockOffersVariantsAndOptions(): void
    {
        $styles = new EventCardBlock()->styles();

        self::assertSame('plain', $styles->defaultVariant()?->name);
        self::assertTrue($styles->offers(BlockOption::Background));
        self::assertFalse($styles->offers(BlockOption::FormLayout));
    }

    public function testCoreEventsAreUsableWithoutCore(): void
    {
        $event = new PageTransitioned(4, 'publish', 'approved', 'published', 2);

        self::assertTrue($event->published());
    }

    public function testSmsAdapterSendsLooksUpAndReadsWebhooks(): void
    {
        $provider = new TestSmsProvider();
        $connection = new SmsConnection('test-key-123456', sender: 'Demo');
        $number = new PhoneNumber('+447700900123');

        self::assertSame('test', $provider->id());
        self::assertNotSame('', $provider->label() . $provider->description());
        self::assertTrue($provider->capabilities()->delivers('PT'));

        $result = $provider->send(new SmsMessage($number, 'Your Demo code is 123456.', SmsPurpose::SecurityCode), $connection);
        self::assertSame('msg-1', $result->messageId);
        self::assertCount(1, $provider->sent);

        self::assertTrue($provider->lookup($number, $connection)->isMobile());
        self::assertSame(PhoneLineType::PremiumRate, $provider->lookup(new PhoneNumber('+449090123456'), $connection)->lineType);
        self::assertFalse($provider->lookup(new PhoneNumber('+351912345678'), $connection)->valid);

        $body = json_encode(['receipts' => [['id' => 'msg-1', 'ok' => true], ['id' => 'msg-2', 'ok' => false]], 'stop' => ['+447700900123']], JSON_THROW_ON_ERROR);
        $request = new ServerRequest('POST', '/sms/webhook', ['X-Test-Signature' => hash_hmac('sha256', $body, $connection->key)], $body);
        $reports = $provider->readWebhook($request, $connection);
        self::assertCount(3, $reports);
        self::assertInstanceOf(SmsReceipt::class, $reports[0]);
        self::assertSame(SmsDeliveryStatus::Delivered, $reports[0]->status);
        self::assertInstanceOf(SmsReceipt::class, $reports[1]);
        self::assertSame('Handset unreachable', $reports[1]->reason);
        self::assertInstanceOf(SmsOptChange::class, $reports[2]);
        self::assertTrue($reports[2]->stopped);

        try {
            $provider->readWebhook($request->withHeader('X-Test-Signature', 'forged'), $connection);
            self::fail('Forged webhook accepted.');
        } catch (SmsException $e) {
            self::assertSame(SmsFailure::InvalidSignature, $e->failure);
        }

        $this->expectException(SmsException::class);
        $provider->send(new SmsMessage($number, 'Hi'), new SmsConnection('wrong'));
    }
}
