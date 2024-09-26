<?php

namespace Fire\Robo\Plugin\Commands;

use Robo\Symfony\ConsoleIO;

/**
 * Provides a command to run all require drush task post a DB import.
 */
class DrushDeployCommand extends FireCommandBase {

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
    $tasks->addTask($this->taskExec($this->getFireExecutable() . ' drush cr'));
    $tasks->addTask($this->taskExec($this->getFireExecutable() . ' drush updb -- -y'));
    $tasks->addTask($this->taskExec($this->getFireExecutable() . ' drush cr'));
    $tasks->addTask($this->taskExec($this->getFireExecutable() . ' drush cim -- -y'));
    $tasks->addTask($this->taskExec($this->getFireExecutable() . ' drush cr'));
    $tasks->addTask($this->taskExec($this->getFireExecutable() . ' drush cim -- -y'));
    $tasks->addTask($this->taskExec($this->getFireExecutable() . ' drush deploy:hook -- -y'));
    return $tasks;
  }
}
