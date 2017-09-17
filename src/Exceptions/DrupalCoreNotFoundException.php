<?php

namespace NuvoleWeb\DrupalComponentScaffold\Exceptions;

/**
 * Class DrupalCoreNotFoundException.
 *
 * @package NuvoleWeb\DrupalComponentScaffold\Exceptions
 */
class DrupalCoreNotFoundException extends \RuntimeException {

  /**
   * DrupalCoreNotFoundException constructor.
   */
  public function __construct() {
    parent::__construct("Package 'drupal/core' not found among project dependencies.");
  }

}
