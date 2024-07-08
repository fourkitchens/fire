<?php

namespace Fire\Robo\Plugin\Commands;

use Robo\Symfony\ConsoleIO;
use Fire\Robo\Plugin\Commands\FireCommandBase;
use Robo\Robo;

/**
 * Provides a command to Power Off your local env.
 */
class EnvPowerOffCommand extends FireCommandBase {

  /**
   * Power Off the local Docker based env (lando, ddev);
   *
   * Usage Example: fire poweroff
   *
   * @command env:poweroff
   * @aliases po, poweroff
   *
   */
  public function envPowerOff(ConsoleIO $io) {
    $env = Robo::config()->get('local_environment');
    return $this->taskExec($env . ' poweroff')->printOutput(TRUE)->run();
  }

}
