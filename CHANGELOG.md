# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [0.6.0] - 2026-10-28

### Added

- Added Nette Tester tests (DI extension, target entities, relative templates, `DropZoneControl` signals, compilation of the default templates with Latte 3, the bridges to `68publishers/image-storage` and `68publishers/notification-bundle`) and GitHub Actions (coding style, tests on PHP 8.3, 8.4 and 8.5 with highest dependencies and on PHP 8.3 with lowest dependencies) and a docker dev environment with PHP 8.3, 8.4 and 8.5.
- Added support for `doctrine/orm` 3.
- Added tests of the entity mapping combined with annotation-mapped (`doctrine/orm` 2) and attribute-mapped application entities.
- Added support for `symfony/event-dispatcher` 8 and `symfony/translation` 8 (through `68publishers/event-dispatcher-extra` 1.4 and `68publishers/translation-bridge` 3).
- Declared directly used dependencies `doctrine/orm` (`^2.16 || ^3.0`), `nette/http`, `nette/schema` and `symfony/event-dispatcher`.

### Changed

- Minimal PHP version is 8.3 (BC break).
- The entities are mapped with PHP attributes instead of annotations and registered with `EntityMapping::DRIVER_ATTRIBUTE` (BC break: a mapping driver service of the type `Doctrine\ORM\Mapping\Driver\AttributeDriver` is required in the container). The database schema is unchanged.
- The mapped superclass `AbstractFile` is registered also when a custom entity is configured (BC break: its lifecycle callback `onPreUpdate()` is now called for custom entities extending `AbstractFile`, the annotation driver of the application ignored it).
- The soft-deletable entity requires `gedmo/doctrine-extensions` 3.5 or newer (attributes support, declared as a conflict).
- Requires `68publishers/doctrine-bridge` `^2.0`, `68publishers/doctrine-persistence` `^0.4`, `68publishers/event-dispatcher-extra` `^1.4`, `68publishers/file-storage` `^2.0`, `68publishers/smart-nette-component` `^2.0` and `68publishers/translation-bridge` `^3.0` (BC break).
- Minimal versions are `doctrine/orm` 2.16 (the `reportFieldsWhereDeclared` mode of the `AttributeDriver`, required when an annotation-mapped entity extends `AbstractFile`), `nette/application` 3.1.10, `nette/di` 3.1.3 and `nette/utils` 3.2.10.
- `TranslatableHtmlStringInterface`, `TranslatableHtmlStringTrait`, `ProgressBar` and `DropZoneControl::addContentHtml()` use `Nette\Localization\Translator` and `Nette\HtmlStringable` instead of the deprecated `ITranslator` and `IHtmlString` (aliases, existing implementations keep working).
- `DeleteFileSourceEventSubscriber` uses `getObjectManager()` instead of the deprecated `getEntityManager()` of the event args.
- Replaced deprecated `Strings::startsWith()` and `Strings::endsWith()` with `str_starts_with()` and `str_ends_with()`.
- Migrated PHP-CS-Fixer to v3 (`.php-cs-fixer.dist.php`), updated `nette/tester` to `^2.6`.
- The integrations are tested with `68publishers/image-storage` `^2.0` and `68publishers/notification-bundle` `^0.4` (dev dependencies).

### Removed

- Removed the unused dependency `nette/forms` (BC break if your project relies on it being installed by this package).
- Removed Travis CI and Scrutinizer configurations.

### Fixed

- The option `extends` of `file_managers` always threw an exception (it looked for the extended manager in the non-existent option `image_managers`). It is now resolved on the raw configuration, so only explicitly set options override the options of the extended file manager.
- Relative templates (`template: '@file.latte'` and `dropzone.template`) are resolved relatively to the directory with the default templates. The `@` was never stripped, and the value was resolved as a service reference by Nette DI.
- `FileBundleExtension::getTargetEntities()` returned a string instead of a `TargetEntity` for soft-deletable entities.
- `DropZoneControl::handleCompleted()` without the files count caused a `TypeError`.
- `DropZoneControl::handleUpload()` caused a `TypeError` when the content type of an unsupported file could not be detected.
- `NotificationEventSubscriber` caused a fatal error after the warning about a presenter without the method `redrawMessages()`.
- PHP 8.4 deprecation of the implicitly nullable parameter `$previous` in `TranslatableException::__construct()`.
- An invalid character in the message of the exception thrown by `SessionAssociationStorage::setNamespace()`.

## [0.5.7] - 2025-05-29

### Fixed

- Fixed PHP 8.4 issues.

## [0.5.6] - 2024-08-07

### Fixed

- Fixed duplicated success upload message.

## [0.5.5] - 2024-08-07

### Fixed

- Fixed removing a file when the entity manager is closed.

## [0.5.4] - 2024-08-06

### Changed

- Allowed `nette/utils` v4.

## [0.5.3] - 2024-07-27

### Changed

- Updated to work with `file-storage` `^1.1.2` and `^1.3`.

## [0.5.2] - 2023-08-23

### Changed

- Replaced `n:inner-snippet` macro with `n:snippet`.

## [0.5.1] - 2023-08-11

### Added

- Added image metadata `number_of_frames`.

## [0.5.0] - 2023-01-04

### Changed

- Added PHP 8.1 into `composer.json` and support warning into the README.

## [0.4.3] - 2022-04-30

### Fixed

- Fixed error messages in `NotificationEventSubscriber`.

## [0.4.2] - 2021-04-12

### Added

- Download action and JS file `client-side/download.js`.

## [0.4.1] - 2021-03-30

### Changed

- A transaction in the manipulator `DeleteManipulator` returns a File entity instead of the File's source.

## [0.4] - 2021-01-29

### Changed

- Integration of `image-storage` in version `^0.5`.
- Nette 3.

[Unreleased]: https://github.com/68publishers/file-bundle/compare/v0.6.0...HEAD
[0.6.0]: https://github.com/68publishers/file-bundle/compare/v0.5.7...v0.6.0
[0.5.7]: https://github.com/68publishers/file-bundle/compare/v0.5.6...v0.5.7
[0.5.6]: https://github.com/68publishers/file-bundle/compare/v0.5.5...v0.5.6
[0.5.5]: https://github.com/68publishers/file-bundle/compare/v0.5.4...v0.5.5
[0.5.4]: https://github.com/68publishers/file-bundle/compare/v0.5.3...v0.5.4
[0.5.3]: https://github.com/68publishers/file-bundle/compare/v0.5.2...v0.5.3
[0.5.2]: https://github.com/68publishers/file-bundle/compare/v0.5.1...v0.5.2
[0.5.1]: https://github.com/68publishers/file-bundle/compare/v0.5.0...v0.5.1
[0.5.0]: https://github.com/68publishers/file-bundle/compare/v0.4.3...v0.5.0
[0.4.3]: https://github.com/68publishers/file-bundle/compare/v0.4.2...v0.4.3
[0.4.2]: https://github.com/68publishers/file-bundle/compare/v0.4.1...v0.4.2
[0.4.1]: https://github.com/68publishers/file-bundle/compare/v0.4...v0.4.1
[0.4]: https://github.com/68publishers/file-bundle/compare/v0.3.3...v0.4
