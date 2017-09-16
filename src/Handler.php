<?php

namespace NuvoleWeb\DrupalComponentScaffold;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Package\RootPackage;

/**
 * Class Handler.
 *
 * @package NuvoleWeb\DrupalComponentScaffold
 */
class Handler {

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
   * Composer plugin options.
   *
   * @var array
   */
  protected $options = [
    'build-root' => 'build',
  ];

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

    /** @var \Composer\Package\RootPackage $package */
    $package = $composer->getPackage();
    $this->package = $package;
    $this->setupOptions($package);
    $this->setupInstallerPaths($package);
    $this->setupScripts($package);
  }

  /**
   * Setup development build.
   */
  public function setupDevelopmentBuild() {

    // Ensure directory exists.
    if (!file_exists($this->getProjectRoot())) {
      mkdir($this->getProjectRoot(), 0755, TRUE);
    }

    // Symlink project.
    $symlink = $this->getProjectRoot() . '/' . $this->getProjectName();
    if (!file_exists($symlink)) {
      symlink($this->getSymlinkTarget($symlink), $symlink);
    }

    // Make sites/default directory writable.
    chmod($this->getDefaultDirectory(), 0755);

    // Setup Drush configration file.
    $content = file_get_contents(__DIR__ . '/../dist/drushrc.php');
    $content = str_replace('BUILD_ROOT', $this->options['build-root'], $content);
    $filename = $this->getDefaultDirectory() . '/drushrc.php';
    file_put_contents($filename, $content);
  }

  /**
   * Get default site directory location.
   *
   * @return string
   *   Default directory location.
   */
  protected function getDefaultDirectory() {
    return $this->options['build-root'] . '/sites/default';
  }

  /**
   * Get symlink target.
   *
   * @param string $symlink
   *   Symlink location.
   *
   * @return string
   *   Symlink target.
   */
  protected function getSymlinkTarget($symlink) {
    $parts = count(explode('/', $symlink));
    return implode('/', array_fill(0, $parts - 1, '..'));
  }

  /**
   * Get project root.
   *
   * @return string
   *   Project root.
   */
  protected function getProjectRoot() {
    switch ($this->getProjectType()) {
      case 'drupal-module':
        return $this->options['build-root'] . '/modules/custom';

      case 'drupal-theme':
        return $this->options['build-root'] . '/themes/custom';

      default:
        throw new \InvalidArgumentException("Only modules and themes are supported.");
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
   * Setup plugin options.
   *
   * @param \Composer\Package\RootPackage $package
   *   Package object.
   */
  protected function setupOptions(RootPackage $package) {
    $extra = $package->getExtra() + [self::PLUGIN_KEY => $this->options];
    $package->setExtra($extra);
  }

  /**
   * Setup Composer Installer paths.
   *
   * @param \Composer\Package\RootPackage $package
   *   Package object.
   */
  protected function setupInstallerPaths(RootPackage $package) {
    $extra = $package->getExtra();
    $extra['installer-paths'] = [
      $this->options['build-root'] . '/core' => ['type:drupal-core'],
      $this->options['build-root'] . '/libraries/{$name}' => ['type:drupal-library'],
      $this->options['build-root'] . '/modules/contrib/{$name}' => ['type:drupal-module'],
      $this->options['build-root'] . '/profiles/contrib/{$name}' => ['type:drupal-profile'],
      $this->options['build-root'] . '/themes/contrib/{$name}' => ['type:drupal-theme'],
    ];
    $package->setExtra($extra);
  }

  /**
   * Force scaffolding to run at every install/update.
   *
   * @param \Composer\Package\RootPackage $package
   *   Package object.
   */
  protected function setupScripts(RootPackage $package) {
    $scripts = $package->getScripts();
    $scripts['post-install-cmd'] = isset($scripts['post-install-cmd']) ? $scripts['post-install-cmd'] : [];
    $scripts['post-install-cmd'] = isset($scripts['post-update-cmd']) ? $scripts['post-install-cmd'] : [];
    $scripts['post-install-cmd'][] = "DrupalComposer\\DrupalScaffold\\Plugin::scaffold";
    $scripts['post-install-cmd'][] = "NuvoleWeb\\DrupalComponentScaffold\\Plugin::scaffold";
    $scripts['post-update-cmd'][] = "DrupalComposer\\DrupalScaffold\\Plugin::scaffold";
    $scripts['post-install-cmd'][] = "NuvoleWeb\\DrupalComponentScaffold\\Plugin::scaffold";
    $package->setScripts($scripts);
  }

}
