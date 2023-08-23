<?php

namespace Fire\Robo\Plugin\Commands;

use Robo\Symfony\ConsoleIO;
use Fire\Robo\Plugin\Commands\FireCommandBase;
use Robo\Robo;

/**
 * Setups a project from scratch.
 */
class LocalSetupCommand extends FireCommandBase {

  /**
   * Setups your project from scratch (lando, ddev);
   *
   * Usage Example: fire build-php
   *
   * @command local:setup
   * @aliases setup
   *
   */
  public function envStop(ConsoleIO $io) {
    $env = Robo::config()->get('local_environment');
    $tasks = $this->collectionBuilder($io);
    switch ($env) {
      case 'lando':
        $tasks->addTask($this->taskExec($env . ' destroy -y'));
        $tasks->addTask($this->taskExec($env . ' rebuild -y'));
        break;
      case 'ddev':
        $tasks->addTask($this->taskExec($env . ' poweroff -y'));
        $tasks->addTask($this->taskExec($env . ' delete -y'));
        $tasks->addTask($this->taskExec($env . ' start'));
    }
    $tasks->addTask($this->taskExec('fire local:build'));
    return $tasks;
  }
}
