<?php

namespace Fire\Robo\Plugin\Commands;

use Robo\Symfony\ConsoleIO;
use Robo\Robo;
use Robo\Tasks;

/**
 * Provides a command to build all php dependencies.
 */
class BuildPhpCommand extends Tasks {

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
    $env = Robo::config()->get('environment');
    $tasks = $this->collectionBuilder($io);
    $tasks->addTask($this->taskExec($env . ' composer install'));
    return $tasks;
  }
}
