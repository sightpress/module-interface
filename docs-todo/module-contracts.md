# Module contracts (0.6)

## What it does

The contracts a SightPress module implements to add features: a manifest that says who the module is and what it
needs, and one `register()` method that declares everything the module adds. Core's own features use the same
contracts as third-party modules.

## Who uses it

Developers who write modules. Site admins never see the contracts; they see what modules add (Features screen,
settings, admin menu entries). No permissions are needed to use the package itself.

## What a module can add (all through `ModuleRegistrar`)

| Sub-registrar    | Adds                                                     | Type used                                  |
|------------------|----------------------------------------------------------|--------------------------------------------|
| `services()`     | services in the site container (private unless public)   | factory closure or autowired class         |
| `routes()`       | HTTP routes with PSR-15 handlers and middleware          | `Routing\Route`                            |
| `blockTypes()`   | content block types                                      | `Block\BlockType` class name               |
| `permissions()`  | permissions and the roles that get them by default       | `Access\Permission`                        |
| `migrations()`   | database migrations in the module's own namespace        | `Migration\MigrationProvider`              |
| `adminMenu()`    | admin navigation entries                                 | `Admin\AdminMenuItem`                      |
| `views()`        | template folders under `@<namespace>`                    | namespace + folder                         |
| `subscribers()`  | event listeners (PSR-14 events)                          | event class + invokable listener class     |
| `commands()`     | `bin/sightpress` console commands                        | `Console\Command` class name               |
| `settings()`     | per-site settings                                        | `Setting\Setting`                          |
| `search()`       | site search documents, or a replacement back-end (0.4)   | `Search\SearchDocumentProvider`, `Search\SearchBackend` class names |
| `dashboard()`    | admin dashboard widgets (0.4)                            | `Dashboard\DashboardWidget` class name     |
| `assistant()`    | AI assistant providers and tasks (0.4)                   | `Assistant\AssistantProvider`, `Assistant\AssistantTask` class names |
| `seo()`          | public pages for the sitemap, `llms.txt` and JSON-LD (0.5) | `Seo\SeoProvider` class name             |
| `sms()`          | text message providers (0.5)                             | `Sms\SmsProvider` class name               |
| `schedule()`     | background work for `schedule:run` (0.5)                 | `Schedule\ScheduledJob` class name         |
| `translations()` | XLIFF translations in the module's own domain (0.5)      | folder of `<domain>.<locale>.xlf` files    |
| `privacy()`      | personal data sets, cookies, processors, consent purposes (0.6) | `Privacy\PersonalDataProvider`, `Privacy\ProcessorSource` class names, `CookieDeclaration`, `ConsentPurpose` |
| `placeholders()` | details editors can put in text (0.6)                    | `Placeholder\Placeholder`                  |

`blockTypes()` also takes `addVariant()` (a variant for another block type) and `addIcon()` (0.5), and
`addCodeLanguage()` (0.6).

## Manifest

`ModuleManifest`: id (`page`, `acme.events`), SemVer version, required modules with version constraints
(`ModuleRequirement`), `required` (always on) and `enabledByDefault` (ignored for required modules).
Invalid values throw `Exception\InvalidValueException` with a message that names the field and the rule.

## Permissions and sign-up (0.2)

`Permission` has `allowedOnSignUp` (bool, default `false`). People who register themselves (self-registration or
domain registration) can only get a role whose permissions are all `allowedOnSignUp`, so they can never get admin
rights. A module's new permission is safe by default; set `allowedOnSignUp: true` only for low-risk actions on the
person's own content, never for managing users, roles, settings or other people's content. Core checks the sign-up
role when it is chosen and again at registration (falling back to `member`). Example:
`new Permission('events.suggest', 'Suggest events', allowedOnSignUp: true)`.

## Site search (0.4)

- A `SearchDocumentProvider` has a `source()` name (usually the module id), returns every document in a locale
  (`documents()`, a generator for big sites) and one document by ref (`document()`, null when it is gone,
  unpublished or left out of search). Only published content, as plain text, with its visibility
  (`SearchVisibility::Public` or `Members`).
- `SearchDocument`: source, ref, locale (BCP 47, e.g. `pt-PT`), site-relative path, title, `updatedAt` (stored in
  UTC), then optional summary, body, breadcrumb and visibility. `key()` is `source:ref:locale`.
- A `SearchBackend` has `index()`, `remove()`, `clear()`, `query(SearchQuery): SearchResults` and `status()`.
  Core ships one that keeps the index in the site database; `search()->useBackend()` replaces it (one module at
  most).
- `SearchQuery`: text (at most 200 characters), locale, `includeMembersOnly` (only for signed-in people), page,
  per page (1–50). `SearchResults` refuses hits in another locale or members-only hits for anonymous queries,
  and says whether every word matched (`matchedAllTerms`) or results fell back to any word.
- `SearchPerformed` (normalised query, locale, result count) is dispatched after each search for analytics.

## Dashboard widgets (0.4)

`DashboardWidget`: `id()`, `title()` (the `<h2>` core renders), `permission()` (null = every admin user),
`position()` and `render(DashboardContext)` for the content below the heading. The context gives the viewer's
locale, time zone, `can()` and `url()` for named routes.

## AI assistant (0.4)

- Optional and off by default; every site brings its own key. A provider (`AssistantProvider`) has an id, label,
  plain-language description (who runs it, where requests are processed, how long they are kept), models, whether
  it needs a base URL, per-model `capabilities()` (vision, streaming) and `complete(AssistantRequest,
  AssistantConnection)`. `StreamingAssistantProvider` adds `stream()`, a generator of text pieces that returns
  the whole response.
- `AssistantConnection` holds the API key (hidden from dumps, `maskedKey()` shows `••••1234`, can't be
  serialised) and an optional base URL.
- `AssistantRequest`: model, messages (`AssistantMessage::system()` first, ending with `::user()`; images
  only on user messages), most output tokens, optional temperature 0–1. `AssistantResponse`: text, model,
  `TokenUsage`, `truncated`.
- A task (`AssistantTask`): id, label, permission, input JSON Schema, `needsVision()`, `buildPrompt()` and
  `validateOutput()`, which returns an `AssistantSuggestion` (text + form fields for *Insert*) or throws
  `AssistantException` with `AssistantFailure::InvalidOutput`. Nothing an assistant does can change the site.
- Providers report failures with `AssistantException` and an `AssistantFailure` (invalid key, quota, rate
  limit, timeout, model unavailable, refused, unavailable); messages never contain the key, prompt or answer.

## Events (0.5)

Core dispatches the classes in `SightPress\Module\Event` (part of the 1.0 API): `PageCreated`, `PageSaved`,
`PageMoved`, `PageTransitioned` (with `published()` / `unpublished()`), `PageCloned`, `PagePurged`,
`PageScheduled`, `ScheduledStepFailed`, `RevisionRestored`, `TranslationPublished`, `RedirectChanged`,
`MediaUploaded`, `MediaChanged`, `MediaDeleted`, `MenuChanged`, `AccountRegistered`, `AccountChanged`,
`RoleChanged`, `SettingsChanged`, `LocaleRemoved`, `SiteModuleChanged`, `ThemeApplied`, `ThemeColoursChanged`,
`AnalyticsDataDeleted`, `AssistantUsed`, plus `Search\SearchPerformed`. They carry ids and plain values (workflow
steps and states as strings, listed in constants), never personal data such as email addresses. Listen with
`subscribers()->listen(PageTransitioned::class, MyListener::class)`.

## Screens, access and site services (0.5)

From the site's container, in a handler's constructor:

- `Http\Screens`: `admin()` renders an `AdminScreen` (title, a template of the module, variables, status) inside
  the admin panel; `public()` renders a `PublicScreen` in the site's theme, with its `SeoEntry` for the head;
  `redirect()` sends a `303` with an optional notice. Titles and notices are text or a `Text\Message` (translated
  by core, with `{name}` placeholders and the module's own domain).
- `Access\Viewer::fromRequest($request)`: the signed-in account id and `can('events.manage')`.
- `Site\SiteInfo`: id, slug, name, default locale, locales.
- `Setting\SettingValues::get('events.page_size')`: typed value or default.
- `Audit\AuditTrail::record(new AuditRecord('acme.events.event_deleted', 'Deleted …', $viewer->userId))`.

Routes: `new Route(…, permission: 'events.manage')` makes core check sign-in and the permission (plus
`admin.access` under `/admin`); `critical: true` on a `POST` route asks for two-step confirmation where the site
has it. Templates use core's template functions (`t()`, `can()`, `csrf_fields()`, `current_locale()`,
`user_date`), and every `POST` form prints `{{ csrf_fields() }}`.

## SEO, scheduled jobs and translations (0.5)

- `SeoProvider::entries($locale)` lists public, indexable pages as `SeoEntry`: path, locale, title, `updatedAt`
  (UTC), description, Markdown path (`.md`), alternates (locale → path) and schema.org nodes (each with
  `@type`). Core leaves them out on members-only sites, outside production and when the module is off.
- `ScheduledJob::run(ScheduleRun $run)`: use `$run->now`, report with `$run->report()`, never do anything twice.
- Translations: `translations()->add(__DIR__ . '/../translations')` with `acme_events.en-GB.xlf` and others; the
  `messages` domain is core's.

## Blocks: variants, options and icons (0.5)

A block type that implements `StyledBlockType` returns `BlockStyles`: its variants (`BlockVariant`, default
first), the shared `BlockOption`s it offers (`align`, `background`, `width`, `spacing`, `form_layout`,
`form_size`, `button_align`, each with its values, default first) and whether it is a short form (allows the
`inline` layout). Core stores the choices per block and draws them as classes. `BlockIcon`: name, label, SVG
file, hosts and an optional brand colour (`#rrggbb`).

## Text messages (0.5)

- `SmsProvider`: id, label, description (who runs it, where, how long data is kept), `capabilities()`
  (`SmsCapabilities`: lookup, delivery receipts, STOP replies, alphanumeric sender, countries), `send()` →
  `SmsResult` (message id, parts) and `lookup()` → `PhoneLookupResult` (valid, `PhoneLineType`, country).
  Optional `SmsWebhooks::readWebhook()` checks the signature and returns `SmsReceipt`s and `SmsOptChange`s.
- `PhoneNumber` holds E.164 only and shows `masked()` (`+44•••••••123`) in dumps; it can't be serialised and its
  errors never contain the number. `SmsConnection` holds the key (masked, not serialisable), account, region and
  sender. `SmsMessage` works out GSM-7 or UCS-2, length and parts (at most 6), and refuses links in a
  `SmsPurpose::SecurityCode` message.
- Failures: `SmsException` with `SmsFailure` (invalid key or number, unsupported country, opted out, no credit,
  rate limited, timeout, unsupported, invalid signature, unavailable).
- `Notification\NotificationChannel`: `email` or `sms`.

## Privacy (0.6)

Core's ADR-0075 (privacy model) and ADR-0077 (this contract). Plain language, not legal advice.

- `PersonalDataProvider`, one per data set a module keeps about people: `describe()` → `PersonalDataSet` (dotted
  key, label, purpose, `PersonalDataCategory` list, the *suggested* `LawfulBasis`, `Retention` in days or while the
  account exists, `ErasureRule` delete / pseudonymise / keep with a reason); `export(UserRef)` → JSON-safe records;
  `erase(UserRef)` → `ErasureResult` (counts only), safe to run again after a backup restore, files included.
- `UserRef`: account id, email and optional `PhoneNumber`; masked in dumps, not serialisable.
- `#[PersonalData('acme_events.bookings')]` on every property that holds personal data; core fails a field whose
  data set has no provider.
- `CookieDeclaration`: name, purpose, lifetime (seconds, or null for the browser session), strictly necessary or
  not, `StorageKind` (cookie, local or session storage). Not strictly necessary → it names a `ConsentPurpose` and
  waits for `ConsentManager::allows()`.
- `ProcessorSource::processors()` → `ProcessorDeclaration`s (who, what is sent, countries, hosts, privacy policy,
  safeguards), only while the feature is configured. Core's outgoing HTTP client only reaches declared hosts (GD-1).
- `ConsentManager` (site container): `purposes()`, `allows(request, purpose)` (false until chosen; `Sec-GPC: 1`
  is no), `hasConsented()`, `record()` with the text version, `withdraw()`.
- Events: `UserDataExported`, `UserErasureScheduled`, `UserErasureCancelled`, `UserErased` (ids and dates only).

## Placeholders, code languages and formats (0.6)

- `Placeholder`: dotted name (`acme_events.next_date`; `site.*` and `user.*` are core's), label, description,
  `PlaceholderType`, personal or not, resolver class. `PlaceholderResolver::resolve()` returns text, a number, a
  date or null, never HTML. `PlaceholderContext`: locale, and the viewer id only for personal placeholders on
  members-only pages. ADR-0078.
- `CodeLanguage`: id, label, aliases, optional `CodeHighlighter` that returns `CodeToken`s (text + `CodeTokenKind`)
  whose text joins back to the code. ADR-0079.
- `LocaleFormats` (site container): `date()`, `time()`, `dateTime()` with a `FormatStyle`, `monthName()` /
  `weekdayName()` with a `NameWidth`, `firstDayOfWeek()`, `number()`, `percent()`, `fileSize()`, `withLocale()`.
  `Money` (integer minor units + ISO 4217 code) and `MoneyFormatter` (by the currency, the same in every locale).
  `SiteLanguages`: `isMultilingual()`, `locales()`, `default()`; hide language UI on a single-locale site. ADR-0080.

## Accessibility

- `BlockType::render()` must meet WCAG 2.2 AA and escape output; `renderMarkdown()` gives a text alternative
  (alt text, transcripts, absolute links).
- `BlockType::a11yRules()` returns checks that run on save and before publishing; an `A11yIssue` points at the
  field (JSON Pointer) so the editor can move focus there, and lists the WCAG criteria. Errors block publishing,
  warnings don't.
- Admin menu labels must make sense out of context (screen readers list links on their own).
- Console output: plain sentences, no redrawn progress bars, never colour alone.
- Dashboard widgets: the title is a heading that must make sense on its own; content uses `<h3>` and below,
  works without JavaScript and gives every chart a text alternative.
- Assistant: core never streams text into a live region; it announces only short status messages.

## Stability

Interfaces that modules implement (`ModuleInterface`, `BlockType`, `A11yRule`, `Command`, `MigrationProvider`,
`SearchDocumentProvider`, `SearchBackend`, `DashboardWidget`, `AssistantProvider`, `StreamingAssistantProvider`,
`AssistantTask`, `SeoProvider`, `ScheduledJob`, `StyledBlockType`, `SmsProvider`, `SmsWebhooks`,
`PersonalDataProvider`, `ProcessorSource`, `PlaceholderResolver`, `CodeHighlighter`) only change in a
major release. Interfaces that core implements (`ModuleRegistrar`, the
sub-registrars, `BlockContext`, `CommandInput`, `CommandOutput`, `DashboardContext`, `AssistantContext`, `Screens`,
`SettingValues`, `AuditTrail`, `ConsentManager`, `PlaceholderContext`, `LocaleFormats`, `MoneyFormatter`,
`SiteLanguages`) may gain methods in a minor release. `tests/Unit/Contract/ContractShapeTest.php` pins the 0.4, 0.5
and 0.6 shapes.

## Code and tests

- Contracts: `src/`
- Contract tests with fake modules and in-memory registrars: `tests/Unit/Contract/` (registration, search,
  dashboard, assistant, privacy and formats, shapes), fakes in `tests/Fake/` (an in-memory search back-end and an echo assistant
  provider in `tests/Fake/Labs/`)
- Value object tests: `tests/Unit/`
