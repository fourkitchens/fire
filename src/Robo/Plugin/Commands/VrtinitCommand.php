<?php

namespace Fire\Robo\Plugin\Commands;

use Robo\Symfony\ConsoleIO;
use Robo\Robo;

/**
 * Provides a command to initialize VRT.
 */
class VrtinitCommand extends FireCommandBase {

  /**
   * Intialices VRT for your env.
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
      $tasks->addTask($this->taskExec('fire vrt:generate-backstop-config'));
      $tasks->addTask($this->taskExec('fire vrt:local-env-config'));
    }

    return $tasks;
  }
}
