<?php

namespace NuvoleWeb\DrupalComponentScaffold;

use Composer\Script\Event;
use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Util\Filesystem;
use DrupalComposer\DrupalScaffold\Handler as DrupalScaffold;
use NuvoleWeb\DrupalComponentScaffold\Exceptions\DrupalCoreNotFoundException;
use NuvoleWeb\DrupalComponentScaffold\Exceptions\InstallerPathsNotFoundException;
use NuvoleWeb\DrupalComponentScaffold\Exceptions\NotSupportedProjectTypeException;

/**
 * Class Handler.
 *
 * @package NuvoleWeb\DrupalComponentScaffold
 */
class Handler {

  /**
   * Plugin key used in Composer 'script' section and as command name.
   */
  const PLUGIN_KEY = 'drupal-component-scaffold';

  /**
   * Composer instance.
   *
   * @var \Composer\Composer
   */
  protected $composer;

  /**
   * IO Instance.
   *
   * @var \Composer\IO\IOInterface
   */
  protected $io;

  /**
   * Package instance.
   *
   * @var \Composer\Package\RootPackage
   */
  protected $package;

  /**
   * Filesystem utility class.
   *
   * @var \Composer\Util\Filesystem
   */
  protected $fs;

  /**
   * Handler constructor.
   *
   * @param \Composer\Composer $composer
   *   Composer instance.
   * @param \Composer\IO\IOInterface $io
   *   IO instance.
   */
  public function __construct(Composer $composer, IOInterface $io) {
    $this->composer = $composer;
    $this->io = $io;
    $this->package = $composer->getPackage();
    $this->fs = new Filesystem();

    $this->ensureDrupalCore();
    $this->ensureScripts();
    $this->ensurePatches();
  }

  /**
   * Setup development build.
   */
  public function setupDevelopmentBuild() {
    $this->io->write('<info>Running component scaffolding:</info>');
    $this->doSetupDirectories();
    $this->doCreateSymlink();
    $this->doSetupDrush();
    $this->doSetupDevelopmentSettings();
  }

  /**
   * Pre autoload dump event handler.
   *
   * @param \Composer\Script\Event $event
   *   Composer event.
   */
  public function preAutoloadDump(Event $event) {
    $root = $this->getBuildRoot();
    $autoload = $this->package->getDevAutoload();
    $autoload['psr-0']["Drupal\\Tests"] = $root . "/core/tests";
    $autoload['psr-0']["Drupal\\KernelTests"] = $root . "/core/tests";
    $autoload['psr-0']["Drupal\\FunctionalTests"] = $root . "/core/tests";
    $autoload['psr-0']["Drupal\\FunctionalJavascriptTests"] = $root . "/core/tests";
    $autoload['psr-4']["Drupal\\simpletest\\"] = $root . "/core/modules/simpletest/src";
    $this->package->setDevAutoload($autoload);
  }

  /**
   * Setup build directories.
   */
  protected function doSetupDirectories() {
    $destination = $this->getInstallationDirectory();
    $this->write('Prepare custom projects directory at <comment>%s</comment>', $this->shortenDirectory($destination));
    $this->fs->emptyDirectory($destination);

    $destination = $this->getDefaultDirectory();
    $this->write('Make <comment>%s</comment> writable', $this->shortenDirectory($destination));
    chmod($destination, 0755);
  }

  /**
   * Create project symlink.
   */
  protected function doCreateSymlink() {
    $symlink = $this->getInstallationDirectory() . '/' . $this->getProjectName();
    $this->write('Symlink project at <comment>%s</comment>', $this->shortenDirectory($symlink));
    $this->fs->relativeSymlink($this->getProjectDirectory(), $symlink);
  }

  /**
   * Setup Drush configration file.
   */
  protected function doSetupDrush() {
    $content = file_get_contents(__DIR__ . '/../dist/drushrc.php');
    $content = str_replace('BUILD_ROOT', $this->getBuildRoot(), $content);
    $filename = $this->getDefaultDirectory() . '/drushrc.php';
    $this->write('Setup default Drush configuration file at <comment>%s</comment>', $this->shortenDirectory($filename));
    file_put_contents($filename, $content);
  }

  /**
   * Setup Drush configration file.
   */
  protected function doSetupDevelopmentSettings() {
    $source = __DIR__ . '/../dist/development.services.yml';
    $destination = $this->getSitesDirectory() . '/development.services.yml';
    $this->write('Make sure that Twig cache is disabled in <comment>%s</comment>', $this->shortenDirectory($destination));
    copy($source, $destination);

    $destination = $this->getDefaultDirectory() . '/default.settings.php';
    $content = file_get_contents($destination);
    $ignore_directory_setting = sprintf('$settings[\'file_scan_ignore_directories\'][] = \'%s\';', $this->getBuildRoot());
    if (strstr($content, $ignore_directory_setting) === FALSE) {
      file_put_contents($destination, $ignore_directory_setting . PHP_EOL, FILE_APPEND);
    }
    $this->write('Add build directory to list of ignored paths in <comment>%s</comment>.', $this->shortenDirectory($destination));

    $destination = $this->getDefaultDirectory() . '/settings.local.php';
    $source = $this->getSitesDirectory() . '/example.settings.local.php';
    $content = file_get_contents($source);
    $content = str_replace('# $settings[\'cache\'][\'bins\']', '$settings[\'cache\'][\'bins\']', $content);
    file_put_contents($destination, $content);
    $this->write('Setup local development settings at <comment>%s</comment>.', $this->shortenDirectory($destination));
    $this->write('Note: local development settings file is disabled by default, enable it by un-commenting related lines in your settings.php file.');
  }

  /**
   * Get default site directory location.
   *
   * @return string
   *   Default directory location.
   */
  protected function getDefaultDirectory() {
    return $this->getSitesDirectory() . '/default';
  }

  /**
   * Get site directory location.
   *
   * @return string
   *   Sites directory location.
   */
  protected function getSitesDirectory() {
    return $this->getBuildDirectory() . '/sites';
  }

  /**
   * Get site directory location.
   *
   * @return string
   *   Sites directory location.
   */
  protected function getBuildDirectory() {
    return realpath($this->getBuildRoot());
  }

  /**
   * Get build root name from installer paths.
   *
   * @return string
   *   Sites directory location.
   */
  protected function getBuildRoot() {

    $extra = $this->package->getExtra();
    $paths = isset($extra['installer-paths']) ? $extra['installer-paths'] : [];
    foreach ($paths as $path => $types) {
      if (in_array("type:drupal-core", $types)) {
        return str_replace('/core', '', $path);
      }
    }

    throw new InstallerPathsNotFoundException();
  }

  /**
   * Get base project directory.
   *
   * @return string
   *   Sites directory location.
   */
  protected function getProjectDirectory() {
    return realpath('.');
  }

  /**
   * Shorten directory path.
   *
   * @param string $directory
   *   Full directory path.
   *
   * @return string
   *   Shortened directory path.
   */
  protected function shortenDirectory($directory) {
    return str_replace($this->getProjectDirectory(), '.', $directory);
  }

  /**
   * Get project installation directory.
   *
   * @return string
   *   Project root.
   */
  protected function getInstallationDirectory() {
    switch ($this->getProjectType()) {
      case 'drupal-module':
        return $this->getBuildDirectory() . '/modules/custom';

      case 'drupal-theme':
        return $this->getBuildDirectory() . '/themes/custom';

      case 'drupal-drush':
        return $this->getBuildDirectory() . '/sites/all/drush';

      default:
        throw new NotSupportedProjectTypeException();
    }
  }

  /**
   * Return Drupal project type.
   *
   * @return string
   *   Project type.
   */
  protected function getProjectType() {
    return $this->package->getType();
  }

  /**
   * Return Drupal project name.
   *
   * @return mixed
   *   Project name.
   */
  protected function getProjectName() {
    return explode('/', $this->package->getName())[1];
  }

  /**
   * Check whereas Drupal core is among dependencies.
   */
  private function ensureDrupalCore() {
    $packages = $this->composer->getPackage()->getDevRequires();
    if (!array_key_exists('drupal/core', $packages)) {
      throw new DrupalCoreNotFoundException();
    }
  }

  /**
   * Force scaffolding to run at every install/update.
   */
  protected function ensureScripts() {
    $scripts = $this->package->getScripts();
    $scripts[Handler::PLUGIN_KEY][] = "NuvoleWeb\\DrupalComponentScaffold\\Plugin::scaffold";
    $scripts[DrupalScaffold::POST_DRUPAL_SCAFFOLD_CMD][] = "NuvoleWeb\\DrupalComponentScaffold\\Plugin::scaffold";
    $this->package->setScripts($scripts);
  }

  /**
   * Apply patches.
   */
  private function ensurePatches() {
    $extra = $this->package->getExtra();
    $patch = realpath(__DIR__ . '/../dist/kernel-test-base.patch');
    $extra['patches']['drupal/core']['Patch KernelTestBase'] = $patch;
    $this->package->setExtra($extra);
  }

  /**
   * Write log message to current output stream.
   *
   * @param mixed $args
   *   Method arguments.
   */
  protected function write(...$args) {
    $args[0] = ' - ' . $args[0];
    $this->io->write(call_user_func_array('sprintf', $args));
  }

}
