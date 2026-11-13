# SightPress module interface

The contracts that every SightPress module implements. SightPress is a powerful CMS for everyone, designed with
blind and low-vision creators and audiences in mind, built on modern, object-oriented PHP.

This package holds interfaces and small value objects only. It contains no implementation code and depends only on
PSR interfaces. SightPress's own features are built as modules on these contracts, and third-party modules use the
same contracts.

It is versioned on its own, with strict semantic versioning: a new major version means a breaking change in a contract.

## What's in it

- `ModuleInterface`: what every module implements: `manifest()` and `register(ModuleRegistrar $registrar)`.
- `ModuleManifest` and `ModuleRequirement`: the module's id, version, the modules it needs, and whether it is
  always on or on by default for new sites.
- `ModuleRegistrar`: one typed sub-registrar per kind of addition: `services()`, `routes()`, `blockTypes()`,
  `permissions()`, `migrations()`, `adminMenu()`, `views()`, `subscribers()`, `commands()` and `settings()`.
- `Block\BlockType`: a content block with its schema, HTML and Markdown rendering, optional structured data and
  its accessibility checks (`A11yRule`, `A11yIssue`).
- Value objects: `Routing\Route`, `Access\Permission`, `Admin\AdminMenuItem`, `Setting\Setting`,
  `Console\CommandParameter`; plus `Migration\MigrationProvider` and the `Console\Command` contract.

The value objects check their values when they are built and throw `Exception\InvalidValueException` with a
message that names the field and the rule.

Interfaces that modules implement change only in a major release. Interfaces that core implements (the registrars,
`Block\BlockContext`, `Console\CommandInput` and `Console\CommandOutput`) may gain methods in a minor release, so
modules must not implement them.

## Requirements

- PHP 8.5 or later
- `psr/container`, `psr/http-server-handler` and `psr/http-server-middleware`

## Development

This package has no dependencies on the other SightPress repos. To work on it:

```sh
composer install
composer check
```

Composer commands:

- `composer check`: everything that must pass before a commit. It validates `composer.json`, checks the code
  style, runs static analysis, runs the unit suite with the coverage gate, then runs the integration suite.
- `composer test`: both test suites. `composer test:unit` and `composer test:integration` run one suite each.
- `composer test:coverage`: the unit suite with coverage. It fails if unit tests cover less than 100% of the lines
  or methods in `src/`, and it lists what is not covered. `@codeCoverageIgnore` is not allowed. The HTML report
  is written to `build/coverage/html/`.
- `composer fix`: applies Rector refactorings, then fixes the code style.

Coverage needs the PCOV extension, or Xdebug in coverage mode. If your PHP has neither, use the
development image from the [core](https://github.com/sightpress/core) repo (`docker/dev-php/`), which has
PHP 8.5, PCOV and Composer. With `core` cloned next to this repo, run this from this folder:

```sh
docker build -t sightpress/dev-php:8.5 ../core/docker/dev-php
docker run --rm --user "$(id -u):$(id -g)" -e HOME=/tmp -v "$PWD:/workspace" -w /workspace \
  sightpress/dev-php:8.5 composer check
```

### Continuous integration

Every pull request and every push to `main` runs the `CI` workflow (`composer check`): `composer validate`, code
style, PHPStan, the unit suite with the 100% coverage gate, the integration suite and `composer audit`. The
coverage report is uploaded as an artifact.

Commit messages follow [Conventional Commits](https://www.conventionalcommits.org/); release-please uses them to
keep a release pull request and `CHANGELOG.md` up to date.

`main` is protected by the ruleset in `.github/rulesets/main.json`. Changes reach it only through a pull request that
passes the `composer check` check and has one approval. Pull requests are squash-merged. Admins can bypass the ruleset.

## Licence

GNU General Public License v3.0 or later. See [LICENSE](LICENSE).
