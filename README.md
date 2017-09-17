# Drupal Component Scaffold

The *Drupal Component Scaffold* [Composer plugin](https://getcomposer.org/doc/articles/plugins.md) makes Drupal 8 project
maintainers enjoy a modern development experience by providing a fully functional Drupal site right within the project directory.

The project depends on the excellent [Drupal Scaffold](https://github.com/drupal-composer/drupal-scaffold) project and
fires only after (and if) the main scaffolding tasks are ran. 

## Usage

Require it via Composer as follow:

```
$ composer require nuvoleweb/drupal-component-scaffold --dev
```

List your development dependencies (core version, modules, etc.) and run:

```
$ composer update
```

At this point the the plugin will:

- Setup [Composer Installers](https://github.com/composer/installers) paths.
- Register a post-"Drupal Scaffold" handler.

When Drupal Scaffold is fired the plugin will:

 - Prepare a custom projects directory at `./build/modules/custom`.
 - Make `./build/sites/default` writable.
 - Symlink your project at `./build/modules/custom/my_module` (or at `./build/themes/custom/my_theme`).
 - Setup default Drush configuration file at `./build/sites/default/drushrc.php`.
 - Make sure that Twig cache is disabled on `./build/sites/development.services.yml`.
 - Setup local development settings at `./build/sites/default/settings.local.php`.

Note: the local development settings file above is disabled by default, to enable it un-comment the related lines
in your `settings.php` file.

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

You can change the build directory name by overriding `build-root` option as follow:

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

Also, all customization options for [Drupal Scaffold](https://github.com/drupal-composer/drupal-scaffold) still apply,
check its documentation for more. 