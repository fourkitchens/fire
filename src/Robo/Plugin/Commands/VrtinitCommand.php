<?php

namespace Fire\Robo\Plugin\Commands;

use Robo\Symfony\ConsoleIO;
use Robo\Robo;

/**
 * Provides a command to initialize VRT.
 */
class VrtinitCommand extends FireCommandBase {

  /**
   * Configure your local enviroment from scratch to use VRT testing (Lando only).
   *
   * Usage Example: fire vrt:init
   *
   * @command vrt:init
   * @aliases vinit
   *
   */
  public function vrtInit(ConsoleIO $io) {
    $env = Robo::config()->get('local_environment');
    $tasks = $this->collectionBuilder($io);
    if ($env == 'lando') {
      $tasks->addTask($this->taskExec($this->getFireExecutable() . ' vrt:generate-backstop-config'));
      $tasks->addTask($this->taskExec($this->getFireExecutable() . ' vrt:local-env-config'));
    }

    return $tasks;
  }
}
