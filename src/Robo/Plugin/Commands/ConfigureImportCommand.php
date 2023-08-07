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
   * Import config.
   *
   * Usage Example: fire configure:import
   *
   * @command local:configure:import
   * @aliases configure-import, configure_import, cim 
   * @usage -- -y
   *
   * @param $args drush you would like to execute.
   */
  public function configure_import(ConsoleIO $io, array $args) {
    $env = Robo::config()->get('environment');
    $tasks = $this->collectionBuilder($io);
    $tasks->addTask($this->taskExec($env . ' drush cim')->args($args));
    return $tasks;
  }
}
