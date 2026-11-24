<?php

declare(strict_types=1);

namespace SightPress\Module\Tests\Unit\Contract;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use SightPress\Module\Assistant\AssistantConnection;
use SightPress\Module\Assistant\AssistantContext;
use SightPress\Module\Assistant\AssistantImage;
use SightPress\Module\Assistant\AssistantMessage;
use SightPress\Module\Assistant\AssistantProvider;
use SightPress\Module\Assistant\AssistantRequest;
use SightPress\Module\Assistant\AssistantResponse;
use SightPress\Module\Assistant\AssistantSuggestion;
use SightPress\Module\Assistant\AssistantTask;
use SightPress\Module\Assistant\ProviderCapabilities;
use SightPress\Module\Assistant\StreamingAssistantProvider;
use SightPress\Module\Assistant\TokenUsage;
use SightPress\Module\Dashboard\DashboardContext;
use SightPress\Module\Dashboard\DashboardWidget;
use SightPress\Module\ModuleRegistrar;
use SightPress\Module\Registrar\AssistantRegistrar;
use SightPress\Module\Registrar\DashboardRegistrar;
use SightPress\Module\Registrar\SearchRegistrar;
use SightPress\Module\Search\SearchBackend;
use SightPress\Module\Search\SearchDocument;
use SightPress\Module\Search\SearchDocumentProvider;
use SightPress\Module\Search\SearchHit;
use SightPress\Module\Search\SearchIndexStatus;
use SightPress\Module\Search\SearchPerformed;
use SightPress\Module\Search\SearchQuery;
use SightPress\Module\Search\SearchResults;
use SightPress\Module\Access\Viewer;
use SightPress\Module\Audit\AuditRecord;
use SightPress\Module\Audit\AuditTrail;
use SightPress\Module\Block\BlockIcon;
use SightPress\Module\Block\BlockStyles;
use SightPress\Module\Block\BlockVariant;
use SightPress\Module\Block\StyledBlockType;
use SightPress\Module\Event\PageTransitioned;
use SightPress\Module\Http\AdminScreen;
use SightPress\Module\Http\PublicScreen;
use SightPress\Module\Http\Screens;
use SightPress\Module\Registrar\BlockTypeRegistrar;
use SightPress\Module\Registrar\ScheduleRegistrar;
use SightPress\Module\Registrar\SeoRegistrar;
use SightPress\Module\Registrar\SmsRegistrar;
use SightPress\Module\Registrar\TranslationRegistrar;
use SightPress\Module\Routing\Route;
use SightPress\Module\Schedule\ScheduledJob;
use SightPress\Module\Seo\SeoEntry;
use SightPress\Module\Seo\SeoProvider;
use SightPress\Module\Setting\SettingValues;
use SightPress\Module\Site\SiteInfo;
use SightPress\Module\Sms\PhoneLookupResult;
use SightPress\Module\Sms\PhoneNumber;
use SightPress\Module\Sms\SmsCapabilities;
use SightPress\Module\Sms\SmsConnection;
use SightPress\Module\Sms\SmsMessage;
use SightPress\Module\Sms\SmsOptChange;
use SightPress\Module\Sms\SmsProvider;
use SightPress\Module\Sms\SmsReceipt;
use SightPress\Module\Sms\SmsResult;
use SightPress\Module\Sms\SmsWebhooks;
use SightPress\Module\Text\Message;

/**
 * Pins the shape of the 0.4 and 0.5 contracts, so a change shows up in review: adding a constructor argument at the end
 * (with a default) or a method to a core-implemented interface is a minor release; renaming, reordering or
 * removing anything, or adding a method to an interface that modules implement, is a major one.
 */
#[CoversNothing]
final class ContractShapeTest extends TestCase
{
    /**
     * @return iterable<string, array{class-string, list<string>}>
     */
    public static function valueObjects(): iterable
    {
        yield 'SearchDocument' => [SearchDocument::class, ['source', 'ref', 'locale', 'path', 'title', 'updatedAt', '?summary', '?body', '?breadcrumb', '?visibility']];
        yield 'SearchQuery' => [SearchQuery::class, ['text', 'locale', '?includeMembersOnly', '?page', '?perPage']];
        yield 'SearchHit' => [SearchHit::class, ['document', '?score', '?matchedWords']];
        yield 'SearchResults' => [SearchResults::class, ['query', '?hits', '?total', '?matchedAllTerms']];
        yield 'SearchIndexStatus' => [SearchIndexStatus::class, ['locale', 'documents', '?lastIndexedAt']];
        yield 'SearchPerformed' => [SearchPerformed::class, ['query', 'locale', 'resultCount']];
        yield 'AssistantImage' => [AssistantImage::class, ['mediaType', 'data']];
        yield 'AssistantMessage' => [AssistantMessage::class, ['role', 'text', '?images']];
        yield 'AssistantRequest' => [AssistantRequest::class, ['model', 'messages', '?maxOutputTokens', '?temperature']];
        yield 'AssistantConnection' => [AssistantConnection::class, ['apiKey', '?baseUrl']];
        yield 'AssistantResponse' => [AssistantResponse::class, ['text', 'model', '?usage', '?truncated']];
        yield 'AssistantSuggestion' => [AssistantSuggestion::class, ['text', '?fields']];
        yield 'TokenUsage' => [TokenUsage::class, ['?inputTokens', '?outputTokens']];
        yield 'ProviderCapabilities' => [ProviderCapabilities::class, ['?vision', '?streaming']];
        yield 'SeoEntry' => [SeoEntry::class, ['path', 'locale', 'title', 'updatedAt', '?description', '?markdownPath', '?alternates', '?structuredData']];
        yield 'AdminScreen' => [AdminScreen::class, ['title', 'template', '?context', '?status']];
        yield 'PublicScreen' => [PublicScreen::class, ['title', 'template', '?context', '?status', '?seo', '?noindex']];
        yield 'Message' => [Message::class, ['text', '?parameters', '?domain']];
        yield 'Viewer' => [Viewer::class, ['userId', '?permissions']];
        yield 'SiteInfo' => [SiteInfo::class, ['id', 'slug', 'name', 'defaultLocale', 'locales']];
        yield 'AuditRecord' => [AuditRecord::class, ['action', 'summary', '?actorId', '?subjectType', '?subjectId', '?details']];
        yield 'BlockVariant' => [BlockVariant::class, ['name', 'label']];
        yield 'BlockStyles' => [BlockStyles::class, ['?variants', '?options', '?shortForm']];
        yield 'BlockIcon' => [BlockIcon::class, ['name', 'label', 'svgFile', '?hosts', '?brandColour']];
        yield 'PhoneNumber' => [PhoneNumber::class, ['e164']];
        yield 'SmsMessage' => [SmsMessage::class, ['to', 'text', '?purpose', '?reference']];
        yield 'SmsResult' => [SmsResult::class, ['messageId', '?parts']];
        yield 'PhoneLookupResult' => [PhoneLookupResult::class, ['valid', '?lineType', '?country']];
        yield 'SmsCapabilities' => [SmsCapabilities::class, ['?lookup', '?deliveryReceipts', '?optOutReplies', '?alphanumericSender', '?countries']];
        yield 'SmsConnection' => [SmsConnection::class, ['key', '?account', '?region', '?sender']];
        yield 'SmsReceipt' => [SmsReceipt::class, ['messageId', 'status', 'at', '?reason']];
        yield 'SmsOptChange' => [SmsOptChange::class, ['from', 'stopped', 'at']];
        yield 'Route' => [Route::class, ['name', 'path', 'handler', '?methods', '?middleware', '?permission', '?critical']];
        yield 'PageTransitioned' => [PageTransitioned::class, ['pageId', 'transition', 'from', 'to', 'actorId', '?comment']];
    }

    /**
     * @param class-string $class
     * @param list<string> $parameters `?` marks an optional one
     */
    #[DataProvider('valueObjects')]
    public function testValueObjectShape(string $class, array $parameters): void
    {
        $class = new ReflectionClass($class);
        $constructor = $class->getConstructor() ?? self::fail('No constructor.');

        self::assertTrue($class->isFinal() && $class->isReadOnly(), 'final readonly');
        self::assertSame(
            $parameters,
            array_map(
                static fn (ReflectionParameter $p): string => ($p->isOptional() ? '?' : '') . $p->getName(),
                $constructor->getParameters(),
            ),
        );
        $public = array_map(static fn ($p): string => $p->getName(), $class->getProperties(\ReflectionProperty::IS_PUBLIC));
        foreach ($parameters as $parameter) {
            self::assertContains(ltrim($parameter, '?'), $public, 'every argument is readable as a property');
        }
    }

    /**
     * @return iterable<string, array{class-string, list<string>}>
     */
    public static function interfaces(): iterable
    {
        yield 'SearchDocumentProvider' => [SearchDocumentProvider::class, ['source(): string', 'documents(locale): iterable', 'document(ref, locale): ?SightPress\Module\Search\SearchDocument']];
        yield 'SearchBackend' => [SearchBackend::class, ['index(document): void', 'remove(source, ref, ?locale): void', 'clear(?locale): void', 'query(query): SightPress\Module\Search\SearchResults', 'status(): array']];
        yield 'SearchRegistrar' => [SearchRegistrar::class, ['addProvider(provider): void', 'useBackend(backend): void']];
        yield 'DashboardWidget' => [DashboardWidget::class, ['id(): string', 'title(): string', 'permission(): ?string', 'position(): int', 'render(context): string']];
        yield 'DashboardContext' => [DashboardContext::class, ['locale(): string', 'timeZone(): DateTimeZone', 'can(permission): bool', 'url(route, ?parameters): string']];
        yield 'DashboardRegistrar' => [DashboardRegistrar::class, ['add(widget): void']];
        yield 'AssistantProvider' => [AssistantProvider::class, ['id(): string', 'label(): string', 'description(): string', 'models(): array', 'needsBaseUrl(): bool', 'capabilities(model): SightPress\Module\Assistant\ProviderCapabilities', 'complete(request, connection): SightPress\Module\Assistant\AssistantResponse']];
        yield 'StreamingAssistantProvider' => [StreamingAssistantProvider::class, ['stream(request, connection): Generator']];
        yield 'AssistantTask' => [AssistantTask::class, ['id(): string', 'label(): string', 'permission(): string', 'inputSchema(): array', 'needsVision(): bool', 'buildPrompt(input, context): array', 'validateOutput(response, input, context): SightPress\Module\Assistant\AssistantSuggestion']];
        yield 'AssistantContext' => [AssistantContext::class, ['locale(): string', 'can(permission): bool', 'screen(): ?string']];
        yield 'AssistantRegistrar' => [AssistantRegistrar::class, ['addProvider(provider): void', 'addTask(task): void']];
        yield 'SeoProvider' => [SeoProvider::class, ['entries(locale): iterable']];
        yield 'SeoRegistrar' => [SeoRegistrar::class, ['addProvider(provider): void']];
        yield 'Screens' => [Screens::class, ['admin(request, screen): Psr\Http\Message\ResponseInterface', 'public(request, screen): Psr\Http\Message\ResponseInterface', 'redirect(request, path, ?notice): Psr\Http\Message\ResponseInterface']];
        yield 'SettingValues' => [SettingValues::class, ['get(key): string|int|bool|null']];
        yield 'AuditTrail' => [AuditTrail::class, ['record(record): void']];
        yield 'ScheduledJob' => [ScheduledJob::class, ['run(run): void']];
        yield 'ScheduleRegistrar' => [ScheduleRegistrar::class, ['add(job): void']];
        yield 'TranslationRegistrar' => [TranslationRegistrar::class, ['add(directory): void']];
        yield 'StyledBlockType' => [StyledBlockType::class, ['styles(): SightPress\Module\Block\BlockStyles']];
        yield 'BlockTypeRegistrar' => [BlockTypeRegistrar::class, ['add(blockType): void', 'addVariant(blockType, variant): void', 'addIcon(icon): void']];
        yield 'SmsProvider' => [SmsProvider::class, ['id(): string', 'label(): string', 'description(): string', 'capabilities(): SightPress\Module\Sms\SmsCapabilities', 'send(message, connection): SightPress\Module\Sms\SmsResult', 'lookup(number, connection): SightPress\Module\Sms\PhoneLookupResult']];
        yield 'SmsWebhooks' => [SmsWebhooks::class, ['readWebhook(request, connection): array']];
        yield 'SmsRegistrar' => [SmsRegistrar::class, ['addProvider(provider): void']];
    }

    /**
     * @param class-string $interface
     * @param list<string> $methods
     */
    #[DataProvider('interfaces')]
    public function testInterfaceShape(string $interface, array $methods): void
    {
        $class = new ReflectionClass($interface);
        $own = array_filter(
            $class->getMethods(),
            static fn (ReflectionMethod $m): bool => $m->getDeclaringClass()->getName() === $interface,
        );

        self::assertTrue($class->isInterface());
        self::assertSame($methods, array_values(array_map(self::signature(...), $own)));
    }

    public function testModuleRegistrarHasTheNewSubRegistrars(): void
    {
        $registrar = new ReflectionClass(ModuleRegistrar::class);

        $subRegistrars = [
            'search' => SearchRegistrar::class,
            'dashboard' => DashboardRegistrar::class,
            'assistant' => AssistantRegistrar::class,
            'seo' => SeoRegistrar::class,
            'sms' => SmsRegistrar::class,
            'schedule' => ScheduleRegistrar::class,
            'translations' => TranslationRegistrar::class,
        ];
        foreach ($subRegistrars as $method => $type) {
            $returnType = $registrar->getMethod($method)->getReturnType();
            self::assertInstanceOf(ReflectionNamedType::class, $returnType);
            self::assertSame($type, $returnType->getName());
        }
    }

    private static function signature(ReflectionMethod $method): string
    {
        $parameters = array_map(
            static fn (ReflectionParameter $p): string => ($p->isOptional() ? '?' : '') . $p->getName(),
            $method->getParameters(),
        );

        return sprintf('%s(%s): %s', $method->getName(), implode(', ', $parameters), (string) $method->getReturnType());
    }
}
