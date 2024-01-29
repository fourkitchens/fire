<?php

namespace Fire\Robo\Plugin\Commands;

use Robo\Symfony\ConsoleIO;
use Fire\Robo\Plugin\Commands\FireCommandBase;
use Robo\Robo;

/**
 * Provides a command to do the fire initial setup.
 */
class Fireinit extends FireCommandBase {

  /**
   * Stops the local Docker based env (lando, ddev);
   *
   * Usage Example: fire init
   *
   * @command init
   */
  public function fireInit(ConsoleIO $io) {
    $this->taskFilesystemStack()->copy($this->getLocalEnvRoot() . '/vendor/fourkitchens/fire/assets/templates/fire.yml', $this->getLocalEnvRoot() . '/fire.yml')->run();
  }
}
