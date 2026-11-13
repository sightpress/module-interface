# SightPress module interface

The contracts that every SightPress module implements. SightPress is a powerful CMS for everyone, designed with
blind and low-vision creators and audiences in mind, built on modern, object-oriented PHP.

This package holds interfaces and small value objects only. It contains no implementation code and depends only on
PSR interfaces. SightPress's own features are built as modules on these contracts, and third-party modules use the
same contracts.

It is versioned on its own, with strict semantic versioning: a new major version means a breaking change in a contract.

## Requirements

- PHP 8.5 or later

## Development

```sh
composer install
composer check
```

## Licence

GNU General Public License v3.0 or later. See [LICENSE](LICENSE).
