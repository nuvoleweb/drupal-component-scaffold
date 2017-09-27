<?php

namespace DrupalComposer\DrupalScaffold\Tests;

use Composer\Util\Filesystem;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

/**
 * Class PluginTest, heavily inspired to Drupal Scaffold's PluginTest.
 *
 * @see \DrupalComposer\DrupalScaffold\Tests\PluginTest
 * @package DrupalComposer\DrupalScaffold\Tests
 */
class PluginTest extends TestCase {

  /**
   * @var \Composer\Util\Filesystem
   */
  protected $fs;

  /**
   * @var string
   */
  protected $rootDir;

  /**
   * @var string
   */
  protected $tmpDir;

  /**
   * @var string
   */
  protected $drupalDir;

  /**
   * @var string
   */
  protected $tmpReleaseTag;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    $this->fs = new Filesystem();
    $this->rootDir = realpath(realpath(__DIR__ . '/..'));
    $this->tmpDir = $this->rootDir . '/build';
    $this->drupalDir = $this->rootDir . '/build/build';

    // Prepare temp directory.
    $this->ensureDirectoryExistsAndClear($this->tmpDir);
    $this->writeTestReleaseTag();
    $this->writeComposerJson();
    $this->writeModuleInfoFile();

    chdir($this->tmpDir);
  }

  /**
   * {@inheritdoc}
   */
  public function tearDown() {
    $this->fs->removeDirectory($this->tmpDir);
    $this->git(sprintf('tag -d "%s"', $this->tmpReleaseTag));
  }

  /**
   * Test plugin.
   */
  public function testComposerPlugin() {
    $file = $this->drupalDir . '/index.php';
    $this->assertFileNotExists($file, 'Scaffold file should not be exist.');
    $this->composer('install');
    $this->assertFileExists($this->drupalDir . '/core', 'Drupal core is installed.');
    $this->assertFileExists($this->drupalDir . '/modules/custom');
    $this->assertFileExists($this->drupalDir . '/modules/custom/test_module');
    $this->assertFileExists($this->drupalDir . '/modules/custom/test_module/test_module.info.yml');
    $this->assertFileExists($this->drupalDir . '/sites/default/drushrc.php');
    $this->assertFileExists($this->drupalDir . '/sites/default/settings.local.php');
    $this->assertFileExists($this->drupalDir . '/sites/default/default.settings.php');
    $content = file_get_contents($this->drupalDir . '/sites/default/default.settings.php');
    $this->assertContains('$settings[\'file_scan_ignore_directories\'][] = \'build\';', $content);

    $this->assertFileExists($file, 'Scaffold file should be automatically installed.');
    $this->fs->remove($file);
    $this->assertFileNotExists($file, 'Scaffold file should not be exist.');
    $this->composer('drupal-scaffold');
    $this->assertFileExists($file, 'Scaffold file should be installed by "drupal-scaffold" command.');
  }

  /**
   * Writes the default composer json to the temp directory.
   */
  protected function writeComposerJson() {
    $json = json_encode($this->composerJSONDefaults(), JSON_PRETTY_PRINT);
    file_put_contents($this->tmpDir . '/composer.json', $json);
  }

  /**
   * Writes test module info file.
   */
  protected function writeModuleInfoFile() {
    $content = Yaml::dump([
      'name' => 'Test module',
      'type' => 'module',
      'core' => '8.x',
    ]);
    file_put_contents($this->tmpDir . '/test_module.info.yml', $content);
  }

  /**
   * Writes a tag for the current commit.
   */
  protected function writeTestReleaseTag() {
    // Tag the current state.
    $this->tmpReleaseTag = '999.0.' . time();
    $this->git(sprintf('tag -a "%s" -m "%s"', $this->tmpReleaseTag, 'Tag for testing this exact commit'));
  }

  /**
   * Provides the default composer.json data.
   *
   * @return array
   */
  protected function composerJSONDefaults() {
    return [
      'name' => 'drupal/test_module',
      'type' => 'drupal-module',
      'prefer-stable' => TRUE,
      'minimum-stability' => 'dev',
      'require-dev' => [
        'nuvoleweb/drupal-component-scaffold' => $this->tmpReleaseTag,
        'composer/installers' => '~1',
        'drupal/core' => '8.3.0',
      ],
      'repositories' => [
        [
          'type' => 'vcs',
          'url' => $this->rootDir,
        ],
      ],
      'scripts' => [
        'drupal-scaffold' => 'DrupalComposer\\DrupalScaffold\\Plugin::scaffold',
      ],
    ];
  }

  /**
   * Wrapper for the composer command.
   *
   * @param string $command
   *   Composer command name, arguments and/or options.
   *
   * @throws \Exception
   *   Throw exception if Composer returned a non-zero exit code
   */
  protected function composer($command) {
    chdir($this->tmpDir);
    passthru(escapeshellcmd($this->rootDir . '/vendor/bin/composer ' . $command), $exit_code);
    if ($exit_code !== 0) {
      throw new \Exception('Composer returned a non-zero exit code');
    }
  }

  /**
   * Wrapper for git command in the root directory.
   *
   * @param $command
   *   Git command name, arguments and/or options.
   *
   * @throws \Exception
   *   Throw exception if Git returned a non-zero exit code.
   */
  protected function git($command) {
    chdir($this->rootDir);
    passthru(escapeshellcmd('git ' . $command), $exit_code);
    if ($exit_code !== 0) {
      throw new \Exception('Git returned a non-zero exit code');
    }
  }

  /**
   * Makes sure the given directory exists and has no content.
   *
   * @param string $directory
   */
  protected function ensureDirectoryExistsAndClear($directory) {
    if (is_dir($directory)) {
      $this->fs->removeDirectory($directory);
    }
    mkdir($directory, 0777, TRUE);
  }

}
