<?php

namespace NuvoleWeb\DrupalComponentScaffold;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\Capable;
use Composer\Plugin\PluginInterface;
use Composer\Plugin\Capability\CommandProvider as CommandProviderCapability;

/**
 * Composer plugin handling Drupal component scaffolding.
 */
class Plugin implements PluginInterface, Capable {

  /**
   * Handler object.
   *
   * @var \NuvoleWeb\DrupalComponentScaffold\Handler
   */
  protected static $handler;

  /**
   * {@inheritdoc}
   */
  public function activate(Composer $composer, IOInterface $io) {
    self::$handler = new Handler($composer, $io);
  }

  /**
   * Drupal Scaffold event callback.
   *
   * @see \NuvoleWeb\DrupalComponentScaffold\Handler::setupScripts()
   */
  public static function scaffold() {
    self::$handler->setupDevelopmentBuild();
  }

  /**
   * {@inheritdoc}
   */
  public function getCapabilities() {
    return [
      CommandProviderCapability::class => CommandProvider::class,
    ];
  }

}
