# Module contracts (0.4)

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
`AssistantTask`) only change in a major release. Interfaces that core implements (`ModuleRegistrar`, the
sub-registrars, `BlockContext`, `CommandInput`, `CommandOutput`, `DashboardContext`, `AssistantContext`) may gain
methods in a minor release. `tests/Unit/Contract/ContractShapeTest.php` pins the 0.4 shapes.

## Code and tests

- Contracts: `src/`
- Contract tests with fake modules and in-memory registrars: `tests/Unit/Contract/` (registration, search,
  dashboard, assistant, shapes), fakes in `tests/Fake/` (an in-memory search back-end and an echo assistant
  provider in `tests/Fake/Labs/`)
- Value object tests: `tests/Unit/`
