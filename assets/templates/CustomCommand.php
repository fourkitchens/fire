<?php

namespace <namespace>;

use Fire\Robo\Plugin\Commands\FireCommandBase;
use Robo\Symfony\ConsoleIO;
use Robo\Robo;

/**
 * <commandDescription>.
 */
class <commandName> extends FireCommandBase {

  /**
   * <commandDescription>.
   *
   * Usage Example: fire <commandFire>
   *
   * @command <commandFireFull>
   * @aliases <commandAlias>
   */
  public function <commandFunction>(ConsoleIO $io) {
    $env = Robo::config()->get('local_environment');
    $tasks = $this->collectionBuilder($io);
    $tasks->addTask($this->taskExec("$env drush cr"));

    return $tasks;
  }

}
