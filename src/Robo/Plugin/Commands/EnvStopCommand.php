<?php

namespace Fire\Robo\Plugin\Commands;

use Robo\Symfony\ConsoleIO;
use Fire\Robo\Plugin\Commands\FireCommandBase;
use Robo\Robo;

/**
 * Provides a command to stop your local env.
 */
class EnvStopCommand extends FireCommandBase {

  /**
   * Stops the local Docker based env (lando, ddev);
   *
   * Usage Example: fire build-php
   *
   * @command env:stop
   * @aliases stop
   *
   */
  public function envStop(ConsoleIO $io) {
    $env = Robo::config()->get('local_environment');
    return $this->taskExec($env . ' stop')->printOutput(TRUE)->run();
  }
}
