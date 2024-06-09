<?php

namespace Fire\Robo\Plugin\Commands;

use Robo\Symfony\ConsoleIO;
use Robo\Robo;

/**
 * Provides a command to build all php dependencies.
 */
class VrtRunCommand extends FireCommandBase {

  /**
   * Runs your VRT testing over you local env.
   *
   * Usage Example: fire vrt-run
   *
   * @command vrt:run
   * @aliases vrt-run
   *
   */
  public function vrtRun(ConsoleIO $io) {
    $env = Robo::config()->get('local_environment');
    $tasks = $this->collectionBuilder($io);
    if ($env == 'lando') {
      $tasks->addTask($this->taskExec($env . ' ssh -s backstopserver -c "backstop test --config=/app/tests/backstop/backstop-local.json"'));
    }

    return $tasks;
  }
}
