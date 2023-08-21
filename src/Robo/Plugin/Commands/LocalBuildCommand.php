<?php

namespace Fire\Robo\Plugin\Commands;

use Robo\Symfony\ConsoleIO;
use Robo\Robo;
use Robo\Tasks;

/**
 * Provides a commmand to build your site from scratch.
 */
class LocalBuildCommand extends Tasks {

  /**
   * Builds your Drupal Site from the scratch.
   *
   * Usage Example: fire local:build
   *
   * @command local:build
   * @aliases local-build build
   * @option $no-db-import Ignores the database import process (Download & Import).
   * @option $no-db-download Ignores ONLY the DB download, data will be imported from your existing db backup file.
   * @option $get-files Gets the Files from the remote server.
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
