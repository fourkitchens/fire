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
   * Exports sites configuration - none interaction required.
   *
   * Usage Example: fire configure:export
   *
   * @command local:configure:export
   * @aliases configure-export, configure_export, cex
   */
  public function configure_export(ConsoleIO $io) {
    $env = Robo::config()->get('local_environment');
    $tasks = $this->collectionBuilder($io);
    $tasks->addTask($this->taskExec($env . ' drush cex -y'));
    return $tasks;
  }
}
