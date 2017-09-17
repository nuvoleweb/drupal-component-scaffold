# Drupal Component Scaffold

The *Drupal Component Scaffold* [Composer plugin](https://getcomposer.org/doc/articles/plugins.md) makes Drupal 8 project maintainers 
enjoy a modern PHP development experience by providing a fully functional Drupal site right
within the project root directory.

Simply list your project requirements (core version, modules,e etc.) the plgin will take care of the rest, including
symlinking the actual project into its proper location and setting up common development settings files.

For example, the following `composer.json`:

```json
{
  "name": "drupal/my_module",
  "type": "drupal-module",
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

Will result in:

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

The project depends on the excellent [Drupal Scaffold](https://github.com/drupal-composer/drupal-scaffold) project.

## Usage

Require it via Composer as a development dependency:

```
$ composer require nuvoleweb/drupal-component-scaffold --dev
```

After that just run:

```
$ composer install
```

*Drupal Component Scaffold* will kick-in right after, and only if, *Drupal Scaffold* will be invoked.

The final Drupal site will be available in the `./build` directory. You can change that by overriding the `build-root`
option as follow:

```json
{
  "extra": {
    "drupal-component-scaffold": {
      "build-root": "web"
    }
  }
}
```

Component scaffolding can be triggered at any time by running:

```
$ composer drupal-component-scaffold
```

A successful ran will produce the following output:

```   
Running component scaffolding:
 - Prepare custom projects directory at /path/to/my_module/build/modules/custom
 - Make /path/to/my_module/build/sites/default writable
 - Symlink project at /path/to/my_module/build/modules/custom/ui_patterns
 - Setup default Drush configuration file at /path/to/my_module/build/sites/default/drushrc.php
 - Make sure that Twig cache is disabled on /path/to/my_module/build/sites/development.services.yml
 - Setup local development settings at /path/to/my_module/build/sites/default/settings.local.php.
 - Note: local development settings file is disabled by default, enable it by un-commenting related lines in your settings.php file.
```
