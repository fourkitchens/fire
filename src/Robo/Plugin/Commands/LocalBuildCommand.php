<?php

namespace Fire\Robo\Plugin\Commands;

use Robo\Symfony\ConsoleIO;
use Robo\Robo;
use Robo\Tasks;

/**
 * Provides a Drush command proxy.
 */
class LocalBuildCommand extends Tasks {

  /**
   * Builds your Drupal Site from the scratch.
   *
   * Usage Example: fire local:build
   *
   * @command local:build
   * @aliases local-build build
   * @usage -- build
   */
  public function localBuild(ConsoleIO $io, $opts = ['no-db-import' => FALSE, 'no-db-download' => FALSE, 'get-files|f' => FALSE]) {
    $tasks = $this->collectionBuilder($io);
    $tasks->addTask($this->taskExec('fire local:build:php'));
    $tasks->addTask($this->taskExec('fire local:build:js'));
    $tasks->addTask($this->taskExec('fire local:build:theme'));
    if (!$opts['no-db-import']) {
      if (!$opts['no-db-download']) {
        $tasks->addTask($this->taskExec('fire local:get-db'));
      }
      $tasks->addTask($this->taskExec('fire local:import-db'));
    }
    if ($opts['get-files']) {
      // Download Files from remote server.
      echo('Command needs to define.');
    }
    // Deploy Drush Commands.
    $tasks->addTask($this->taskExec('fire local:build:drush-commands'));

    return $tasks;
  }
}
