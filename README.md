# Drupal Component Scaffold

*Drupal Component Scaffold* is a [Composer plugin](https://getcomposer.org/doc/articles/plugins.md) that helps Drupal 8
project maintainers enjoy leaner development workflow: working on modules and themes will be like working on any other
modern PHP component.

Once installed the plugin allows to:

- Specify all project's development dependencies in `require-dev`, like Drupal core, modules, themes or any needed
  testing libraries (PHPUnit, PHPSpec, Behat, etc.). [See an example here](https://github.com/nuvoleweb/ui_patterns/blob/8.x-1.x/composer.json).
- Build a fully functional Drupal site right within the project directory by bundling all listed dependencies by just
  running `composer install`.
- Have the same setup on both local development and continuous integration pipelines. This also leads to
  [cleaner CI configuration files](https://github.com/nuvoleweb/ui_patterns/blob/8.x-1.x/.travis.yml).

The plugin leverages the excellent [Drupal Scaffold](https://github.com/drupal-composer/drupal-scaffold) project and
fires only after (and if) its main scaffolding tasks are ran.

## Usage

Require it via Composer as follow:

```
$ composer require nuvoleweb/drupal-component-scaffold --dev
```

List all your dependencies (core version, modules, etc.) and run:

```
$ composer update
```

For example, take the following `composer.json`:

```json
{
  "name": "drupal/my_module",
  "type": "drupal-module",
  "require": {
    "drupal/ds": "~3"
  },
  "require-dev": {
    "nuvoleweb/drupal-component-scaffold": "*",
    "drush/drush": "~8.0",
    "drupal/core": "~8",
    "drupal/panels": "~4",
  },
  "repositories": [
    {
      "type": "composer",
      "url": "https://packages.drupal.org/8"
    }
  ],
  "conflict": {
    "drupal/drupal": "*"
  }
}
```

Running `composer install` will result in:

```
.
├── build
│   ├── autoload.php
│   ├── core
│   ├── modules
│   │   ├── contrib
│   │   │    └── panels
│   │   └── custom
│   │       └── my_module (symlink to project root)
│   └── sites
│       ├── default
│       │   ├── default.services.yml
│       │   ├── default.settings.php
│       │   ├── drushrc.php
│       │   └── settings.local.php
│       ├── development.services.yml
│       ├── example.settings.local.php
│       └── example.sites.php
├── vendor
├── composer.json
├── composer.lock
├── my_module.info.yml
└── my_module.module
```

## Configuration

You can change the build directory name by overriding the `build-root` option as follow:

```json
{
  "extra": {
    "drupal-component-scaffold": {
      "build-root": "web"
    }
  }
}
```

Also, all options for [Drupal Scaffold](https://github.com/drupal-composer/drupal-scaffold) still apply, check the
project's documentation for more.

Component scaffolding can be triggered at any time by running:

```
$ composer drupal-component-scaffold
```

## Setup PHPUnit tests

To setup [PHPUnit](https://phpunit.de) use the following `phpunit.xml.dist` template:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit bootstrap="vendor/autoload.php" backupGlobals="true" colors="true" >
  <php>
    <ini name="error_reporting" value="32767"/>
    <var name="namespaces" value=""/>
    <ini name="memory_limit" value="-1"/>
    <env name="SIMPLETEST_DB" value="mysql://user:pass@host/database"/>
  </php>
  <testsuites>
    <testsuite>
      <directory>./tests/</directory>
    </testsuite>
  </testsuites>
</phpunit>
```

This will ensure that both [Unit and Kernel](https://www.drupal.org/docs/8/testing/types-of-tests-in-drupal-8) tests
tests will ran correctly. [See an example here](https://github.com/nuvoleweb/ui_patterns/blob/8.x-1.x/phpunit.xml.dist).

## Inner workings

When fired the plugin will:

- Setup [Composer Installers](https://github.com/composer/installers) paths.
- Register a post-"[Drupal Scaffold](https://github.com/drupal-composer/drupal-scaffold)" event handler.

After Drupal Scaffold is done the plugin will:

 - Prepare a custom projects directory at `./build/modules/custom`.
 - Make `./build/sites/default` writable.
 - Symlink your project at `./build/modules/custom/my_module` (or at `./build/themes/custom/my_theme`).
 - Setup default Drush configuration file at `./build/sites/default/drushrc.php`.
 - Make sure that Twig cache is disabled on `./build/sites/development.services.yml`.
 - Setup local development settings at `./build/sites/default/settings.local.php`.
 - Patch Drupal core with [kernel-test-base.patch](dist/kernel-test-base.patch) allowing Kernel tests to run smoothly.

Note: the local development settings file above is disabled by default, to enable it un-comment the related lines
in your `settings.php` file and clear the cache.
