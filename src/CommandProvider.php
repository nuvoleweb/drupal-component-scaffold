<?php

namespace NuvoleWeb\DrupalComponentScaffold;

use Composer\Plugin\Capability\CommandProvider as CommandProviderCapability;
use NuvoleWeb\DrupalComponentScaffold\Commands\RunComponentScaffolding;

/**
 * Class CommandProvider.
 *
 * @package NuvoleWeb\DrupalComponentScaffold
 */
class CommandProvider implements CommandProviderCapability {

  /**
   * {@inheritdoc}
   */
  public function getCommands() {
    return [
      new RunComponentScaffolding(),
    ];
  }

}
