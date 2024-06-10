<?php

namespace Fire\Robo\Plugin\Commands;

use Robo\Symfony\ConsoleIO;
use Robo\Robo;

/**
 * Provides to generate the reference files for backstop.
 */
class VrtReferenceCommand extends FireCommandBase {

  /**
   * Runs your VRT testing over you local env.
   *
   * Usage Example: fire vref
   *
   * @command vrt:reference
   * @aliases vref
   *
   */
  public function vrtReference(ConsoleIO $io) {
    $env = Robo::config()->get('local_environment');
    $tasks = $this->collectionBuilder($io);
    if ($env == 'lando') {
      $tasks->addTask($this->taskExec( $env . ' ssh -s backstopserver -c "cd /app/tests/backstop && backstop reference --config=/app/tests/backstop/backstop-local.json"'));
    }

    return $tasks;
  }
}
