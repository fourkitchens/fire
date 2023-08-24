<?php

namespace Fire\Robo\Plugin\Commands;

use Robo\Symfony\ConsoleIO;
use Fire\Robo\Plugin\Commands\FireCommandBase;
use Robo\Robo;

/**
 * Provides a command to start your local env.
 */
class EnvStartCommand extends FireCommandBase {

  /**
   * Starts the local Docker based env (lando, ddev);
   *
   * Usage Example: fire build-php
   *
   * @command env:start
   * @aliases start
   *
   */
  public function envStart(ConsoleIO $io) {
    $env = Robo::config()->get('local_environment');
    return $this->taskExec($env . ' start')->printOutput(TRUE)->run();
  }
}
