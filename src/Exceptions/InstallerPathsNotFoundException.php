<?php

namespace NuvoleWeb\DrupalComponentScaffold\Exceptions;

/**
 * Class InstallerPathsNotFoundException.
 *
 * @package NuvoleWeb\DrupalComponentScaffold\Exceptions
 */
class InstallerPathsNotFoundException extends \RuntimeException {

  /**
   * InstallerPathsNotFoundException constructor.
   */
  public function __construct() {
    parent::__construct("Installer path for Drupal core not found.");
  }

}
