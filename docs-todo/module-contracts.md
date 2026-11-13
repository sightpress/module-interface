# Module contracts (0.1)

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

## Manifest

`ModuleManifest`: id (`page`, `acme.events`), SemVer version, required modules with version constraints
(`ModuleRequirement`), `required` (always on) and `enabledByDefault` (ignored for required modules).
Invalid values throw `Exception\InvalidValueException` with a message that names the field and the rule.

## Accessibility

- `BlockType::render()` must meet WCAG 2.2 AA and escape output; `renderMarkdown()` gives a text alternative
  (alt text, transcripts, absolute links).
- `BlockType::a11yRules()` returns checks that run on save and before publishing; an `A11yIssue` points at the
  field (JSON Pointer) so the editor can move focus there, and lists the WCAG criteria. Errors block publishing,
  warnings don't.
- Admin menu labels must make sense out of context (screen readers list links on their own).
- Console output: plain sentences, no redrawn progress bars, never colour alone.

## Stability

Interfaces that modules implement (`ModuleInterface`, `BlockType`, `A11yRule`, `Command`, `MigrationProvider`)
only change in a major release. Interfaces that core implements (`ModuleRegistrar`, the sub-registrars,
`BlockContext`, `CommandInput`, `CommandOutput`) may gain methods in a minor release.

## Code and tests

- Contracts: `src/`
- Contract test with a fake module and in-memory registrars: `tests/Unit/Contract/ModuleRegistrationTest.php`,
  fakes in `tests/Fake/`
- Value object tests: `tests/Unit/`
