<?php

namespace Fire\Robo\Plugin\Commands;

use Robo\Symfony\ConsoleIO;
use Robo\Robo;

/**
 * Provides a Import database command.
 */
class ImportDBCommand extends FireCommandBase {

  /**
   * Import database for local envs.
   *
   * Usage Example: fire import-db -- <database-file>.sql.gz
   *
   * @command local:import-db
   * @aliases import-db, db-import, importdb, dbimport, import_db, db_import
   * @usage -- <database-file>.sql.gz
   *
   * @param $args drush you would like to execute.
   */
  public function import_db(ConsoleIO $io, array $args) {
    return $this->run($io, $args);
  }

  /**
   * Run both commands.
   */
  public function run(ConsoleIO $io, array $args) {
    $cmd = '';
    $env = Robo::config()->get('local_environment');
    if (count($args)) {
      if (!file_exists($args[0])) {
        return "The specified file doesn't exist, please provide a file, example: 'fire db-import -- site-db.sql.gz'";
      }
    }
    else {
      if (file_exists($this->getLocalEnvRoot() . '/reference/site-db.sql.gz')) {
        if ($env == 'lando') {
          // Landos absolute path is based in their Conteiners folders.
          $args[0] = '/app/reference/site-db.sql.gz';
        }
        else {
          $args[0] = $this->getLocalEnvRoot() . '/reference/site-db.sql.gz';
        }
      }
      else {
        return 'Database file not found into the /reference folder in your projects root';
      }
    }

    switch ($env) {
      case 'lando':
      default:
        $cmd = 'lando db-import';
        break;
      case 'ddev':
        $cmd = 'ddev import-db --src=' . $args[0];
        unset($args[0]);
        break;
    }

    $tasks = $this->collectionBuilder($io);
    $tasks->addTask($this->taskExec($cmd)->args($args));

    return $tasks;
  }

}
