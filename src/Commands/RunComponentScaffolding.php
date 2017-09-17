<?php

namespace NuvoleWeb\DrupalComponentScaffold\Commands;

use Composer\Command\BaseCommand;
use NuvoleWeb\DrupalComponentScaffold\Handler;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class RunComponentScaffolding.
 *
 * @package NuvoleWeb\DrupalComponentScaffold\Commands
 */
class RunComponentScaffolding extends BaseCommand {

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this->setName('drupal-component-scaffold');
    $this->setDescription('Run Drupal component scaffolding.');
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $handler = new Handler($this->getComposer(), $this->getIO());
    $handler->setupDevelopmentBuild();
  }

}
