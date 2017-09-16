<?php

namespace NuvoleWeb\DrupalComponentScaffold;

use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Installer\PackageEvent;
use Composer\Installer\PackageEvents;
use Composer\Script\Event;
use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;

/**
 * Composer plugin handling Drupal component scaffolding.
 */
class Plugin implements PluginInterface, EventSubscriberInterface {

  /**
   * Handler object.
   *
   * @var \NuvoleWeb\DrupalComponentScaffold\Handler
   */
  protected $handler;

  /**
   * {@inheritdoc}
   */
  public function activate(Composer $composer, IOInterface $io) {
    $this->handler = new Handler($composer, $io);
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return array(
      PackageEvents::POST_PACKAGE_INSTALL => 'postPackage',
      PackageEvents::POST_PACKAGE_UPDATE => 'postPackage',
    );
  }

  /**
   * Post package event behaviour.
   *
   * @param \Composer\Installer\PackageEvent $event
   *   Composer event.
   */
  public function postPackage(PackageEvent $event) {
    $this->handler->setupDevelopmentBuild();
  }

  /**
   * Composer script callback.
   *
   * @param \Composer\Script\Event $event
   *   Composer event.
   */
  public static function scaffold(Event $event) {
    $handler = new Handler($event->getComposer(), $event->getIO());
    $handler->setupDevelopmentBuild();
  }

}
