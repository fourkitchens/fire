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
   * Setups your project from scratch (lando, ddev), all your data will be destroy and rebuild.
   *
   * Usage Example: fire build -y
   *
   * @command local:setup
   * @aliases setup
   * @option $no-db-import Ignores the database import process (No Download & Import).
   * @option $no-db-download Ignores ONLY the DB download, data will be imported from your existing db backup file.
   * @option $get-files Gets the Files from the remote server.
   * @option $y Run the command with no interection required.
   */
  public function localSetup(ConsoleIO $io, $opts = ['no-db-import' => FALSE, 'no-db-download' => FALSE, 'get-files|f' => FALSE, 'y|y' => FALSE]) {
    $env = Robo::config()->get('local_environment');

    $shouldRebuild = FALSE;
    if (!$opts['y']) {
      $confirmation = $io->confirm("This command will destroy all your Enviroment data and rebuild it from scratch.\nDo you want to execute it?", TRUE);
      if ($confirmation) {
        $shouldRebuild = TRUE;
      }
    }
    else {
      $shouldRebuild = TRUE;
      unset($opts['y']);
    }

    if ($shouldRebuild) {
      $this->io()->title('Starting enviroment rebuild...');
      $tasks = $this->collectionBuilder($io);
      switch ($env) {
        case 'lando':
          $tasks->addTask($this->taskExec($env . ' destroy -y'));
          $tasks->addTask($this->taskExec($env . ' rebuild -y'));
          break;
        case 'ddev':
          $tasks->addTask($this->taskExec($env . ' poweroff'));
          $tasks->addTask($this->taskExec($env . ' delete -y'));
          $tasks->addTask($this->taskExec($env . ' start'));
      }

      // Creating opts for the build command.
      $buildOptions = [];
      if ($opts['no-db-import']) {
        $buildOptions[] = '--no-db-import';
      }
      if ($opts['no-db-download']) {
        $buildOptions[] = '--no-db-download';
      }
      if ($opts['get-files']) {
        $buildOptions[] = '--get-files';
      }

      $tasks->addTask($this->taskExec($this->getFireExecutable() . ' local:build')->args($buildOptions));
      $tasks->addTask($this->taskExec($this->getFireExecutable() . ' drush uli'));
      return $tasks;
    }
    $this->io()->title('Your site not will be rebuild...');
  }
}
