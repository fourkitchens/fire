<?php

namespace Fire\Robo\Plugin\Commands;

use Robo\Symfony\ConsoleIO;
use Robo\Robo;
use Robo\Tasks;

/**
 * Import config from a config directory.
 */
class ConfigureImportCommand extends Tasks {

  /**
   * Imports sites configuration - none interaction required.
   *
   * Usage Example: fire configure:import
   *
   * @command local:configure:import
   * @aliases configure-import, configure_import, cim
   */
  public function configure_import(ConsoleIO $io) {
    $env = Robo::config()->get('local_environment');
    $tasks = $this->collectionBuilder($io);
    $tasks->addTask($this->taskExec($env . ' drush cim -y'));
    return $tasks;
  }
}
