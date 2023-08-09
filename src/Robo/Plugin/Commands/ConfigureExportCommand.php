<?php

namespace Fire\Robo\Plugin\Commands;

use Robo\Symfony\ConsoleIO;
use Robo\Robo;
use Robo\Tasks;

/**
 * Export config from a config directory.
 */
class ConfigureExportCommand extends Tasks {

  /**
   * Export config.
   *
   * Usage Example: fire configure:export
   *
   * @command local:configure:export
   * @aliases configure-export, configure_export, cex
   * @usage -- -y
   *
   * @param $args drush you would like to execute.
   */
  public function configure_import(ConsoleIO $io, array $args) {
    $env = Robo::config()->get('local_environment');
    $tasks = $this->collectionBuilder($io);
    $tasks->addTask($this->taskExec($env . ' drush cex')->args($args));
    return $tasks;
  }
}
