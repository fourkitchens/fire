<?php

namespace Fire\Robo\Plugin\Commands;

use Robo\Symfony\ConsoleIO;
use Fire\Robo\Plugin\Commands\FireCommandBase;
use Robo\Robo;

/**
 * Setups a project from scratch.
 */
class LocalSetupCommand extends FireCommandBase {

  /**
   * Setups your project from scratch (lando, ddev);
   *
   * Usage Example: fire build-php
   *
   * @command local:setup
   * @aliases setup
   * @option $no-db-import Ignores the database import process (No Download & Import).
   * @option $no-db-download Ignores ONLY the DB download, data will be imported from your existing db backup file.
   * @option $get-files Gets the Files from the remote server.
   */
  public function envStop(ConsoleIO $io, $opts = ['no-db-import' => FALSE, 'no-db-download' => FALSE, 'get-files|f' => FALSE]) {
    $env = Robo::config()->get('local_environment');
    $tasks = $this->collectionBuilder($io);
    switch ($env) {
      case 'lando':
        $tasks->addTask($this->taskExec($env . ' destroy -y'));
        $tasks->addTask($this->taskExec($env . ' rebuild -y'));
        break;
      case 'ddev':
        $tasks->addTask($this->taskExec($env . ' poweroff -y'));
        $tasks->addTask($this->taskExec($env . ' delete -y'));
        $tasks->addTask($this->taskExec($env . ' start'));
    }
    $tasks->addTask($this->taskExec('fire local:build')->args($opts));
    $tasks->addTask($this->taskExec('fire drush uli'));
    return $tasks;
  }
}
