<?php

namespace Fire\Robo\Plugin\Commands;

use Robo\Symfony\ConsoleIO;
use Robo\Tasks;

/**
 * Provides a command to run all require drush task post a DB import.
 */
class DrushDeployCommand extends Tasks {

  /**
   * Drush Build commands - updb , cr, cim , cr, deploy:hook
   *
   * Usage Example: fire build-drush
   *
   * @command local:build:drush-commands
   * @aliases build-drush
   */
  public function drush(ConsoleIO $io) {
    $tasks = $this->collectionBuilder($io);
    $tasks->addTask($this->taskExec('fire drush updb -- -y'));
    $tasks->addTask($this->taskExec('fire drush cr'));
    $tasks->addTask($this->taskExec('fire drush cim -- -y'));
    $tasks->addTask($this->taskExec('fire drush cr'));
    $tasks->addTask($this->taskExec('fire drush cim -- -y'));
    $tasks->addTask($this->taskExec('fire drush deploy:hook -- -y'));
    return $tasks;
  }
}
