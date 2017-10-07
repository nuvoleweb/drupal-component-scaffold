<?php

namespace NuvoleWeb\DrupalComponentScaffold;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\Capable;
use Composer\Plugin\PluginInterface;
use Composer\Plugin\Capability\CommandProvider as CommandProviderCapability;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;

/**
 * Composer plugin handling Drupal component scaffolding.
 */
class Plugin implements PluginInterface, Capable, EventSubscriberInterface {

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
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      ScriptEvents::PRE_AUTOLOAD_DUMP => 'preAutoloadDump',
    ];
  }

  /**
   * Pre autoload dump event handler.
   *
   * @param \Composer\Script\Event $event
   *   Composer event.
   */
  public function preAutoloadDump(Event $event) {
    self::$handler->preAutoloadDump($event);
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
