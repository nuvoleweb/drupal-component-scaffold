<?php

namespace NuvoleWeb\DrupalComponentScaffold;

use Composer\Script\Event;
use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;

/**
 * Composer plugin handling Drupal component scaffolding.
 */
class Plugin implements PluginInterface {

  /**
   * {@inheritdoc}
   */
  public function activate(Composer $composer, IOInterface $io) {
    new Handler($composer, $io);
  }

  /**
   * Composer script callback.
   *
   * @param \Composer\Script\Event $event
   *   Composer event.
   */
  public static function scaffold(Event $event) {
    $handler = new Handler($event->getComposer(), $event->getIO());
    $handler->setupDevelopment();
  }

}
