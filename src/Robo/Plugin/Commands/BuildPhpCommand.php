<?php

namespace Fire\Robo\Plugin\Commands;

use Robo\Symfony\ConsoleIO;
use Robo\Robo;

/**
 * Provides a command to build all php dependencies.
 */
class BuildPhpCommand extends FireCommandBase {

  /**
   * Builds Project PHP Dependencies.
   *
   * Usage Example: fire build-php
   *
   * @command local:build:php
   * @aliases build-php
   *
   */
  public function buildPhp(ConsoleIO $io) {
    $env = Robo::config()->get('local_environment');
    $tasks = $this->collectionBuilder($io);
    $composerPath = $this->getlocalEnvRoot();
    if ($env == 'lando') {
      $composerPath = '/app';
    }
    $tasks->addTask($this->taskExec($env . ' composer install -d ' . $composerPath));
    return $tasks;
  }
}
