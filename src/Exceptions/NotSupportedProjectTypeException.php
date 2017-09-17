<?php

namespace NuvoleWeb\DrupalComponentScaffold\Exceptions;

/**
 * Class NotSupportedProjectTypeException.
 */
class NotSupportedProjectTypeException extends \RuntimeException {

  /**
   * NotSupportedProjectTypeException constructor.
   */
  public function __construct() {
    parent::__construct("Scaffolding is supported only for Composer projects of type 'drupal-module' and 'drupal-theme'.");
  }

}
