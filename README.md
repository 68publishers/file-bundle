> :warning: Warning! This package does not have active support, it exists only for the historical needs of the author.

# File Bundle

:city_sunset: File management bundle based on our [file-storage](https://github.com/68publishers/file-storage) for Nette Framework.

## Installation

The best way to install 68publishers/file-bundle is using Composer:

```bash
composer require 68publishers/file-bundle
```

Requirements: PHP `^8.3`, `doctrine/orm` `^2.16 || ^3.0`, `nette/application` `^3.1.10`. The soft-deletable entity requires `gedmo/doctrine-extensions` `^3.5`.

The entities are mapped with PHP attributes and registered through `68publishers/doctrine-bridge` with the attribute driver (`EntityMapping::DRIVER_ATTRIBUTE`), so a mapping driver service of the type `Doctrine\ORM\Mapping\Driver\AttributeDriver` must exist in the container. Applications that map their own entities with annotations (`doctrine/orm` 2) only need to register it next to the annotation driver, e.g.:

```neon
services:
    attributeDriver:
        factory: Doctrine\ORM\Mapping\Driver\AttributeDriver([], true)
        autowired: false
```

or use `Nettrine\ORM\DI\OrmAttributesExtension` of `nettrine/orm` (the bridge option `68publishers.doctrine_bridge.services.drivers.attribute` selects the driver service if there are more of them). Annotation-mapped application entities can still extend or relate to the package entities. The driver must be created with `reportFieldsWhereDeclared: true` (the second argument, the only mode of `doctrine/orm` 3), otherwise an annotation-mapped entity extending `AbstractFile` has no fields of the superclass ("No identifier/primary key specified"). `OrmAttributesExtension` of `nettrine/orm` 0.8 creates the driver without it.

## Configuration

```neon
extensions:
    68publishers.file_bundle: SixtyEightPublishers\FileBundle\DI\FileBundleExtension

68publishers.file_bundle:
    entity: SixtyEightPublishers\FileBundle\Entity\Basic\File
    file_managers:
        default:
            template: '@fileManagerControl.latte' # a template relative to the default templates directory or an absolute path
            actions:
                - SixtyEightPublishers\FileBundle\Action\DownloadAction
        readonly:
            extends: default # inherits all options of the file manager `default`, lists (actions, manipulators...) are merged
            max_allowed_files: 1
```

The extended file manager must be defined in the same configuration file.

## Upgrade

### From 0.5 to 0.6

- The minimal PHP version is 8.3.
- The package requires `68publishers/doctrine-bridge` `^2.0`, `68publishers/doctrine-persistence` `^0.4`, `68publishers/event-dispatcher-extra` `^1.4`, `68publishers/file-storage` `^2.0`, `68publishers/smart-nette-component` `^2.0` and `68publishers/translation-bridge` `^3.0`.
- The integrations of `68publishers/image-storage` and `68publishers/notification-bundle` need versions compatible with the packages above (`image-storage` 1.x requires `file-storage` `^1.4`, `notification-bundle` 0.3 requires `smart-nette-component` `^1.0` and `translation-bridge` `^2.0`).
- `nette/forms` is no longer required. Require it in your project if you use it.
- The entities are mapped with PHP attributes instead of annotations (the database schema is the same) and `doctrine/orm` 3 is supported. Register a service of the type `Doctrine\ORM\Mapping\Driver\AttributeDriver` (see [Installation](#installation)), otherwise the container compilation fails with "Can't resolve service definition ... services.drivers.attribute".
- If you use your own entity that extends `AbstractFile`, the mapped superclass is now registered too. Its lifecycle callback `onPreUpdate()` (updates `updated` and the source version) is now called for your entity as well, the annotation driver ignored it before.
- `doctrine/orm` 2.16 is the minimal version (see above). `doctrine/annotations` is no longer required. The soft-deletable entity requires `gedmo/doctrine-extensions` `^3.5` (attributes support).
- `TranslatableHtmlStringInterface::setTranslator()` accepts `Nette\Localization\Translator` and `DropZoneControl::addContentHtml()` accepts `Nette\HtmlStringable` (the deprecated `ITranslator` and `IHtmlString` are aliases of them, existing implementations keep working).

## Contributing

Before committing any changes, don't forget to run

```bash
vendor/bin/php-cs-fixer fix -v --dry-run
```

and

```bash
vendor/bin/tester ./tests
```
