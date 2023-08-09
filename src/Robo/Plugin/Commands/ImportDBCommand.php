<?php

namespace Fire\Robo\Plugin\Commands;

use Robo\Symfony\ConsoleIO;
use Robo\Robo;
use Robo\Tasks;

/**
 * Provides a Import database command.
 */
class ImportDBCommand extends Tasks {

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

    if (!count($args)) {
      $args[0] = 'site-db.sql.gz';
    }

    if (!file_exists($args[0])) {
      return "The specified file doesn't exist, please provide a file, example: 'fire db-import -- site-db.sql.gz'";
    }

    switch ($env) {
      case 'lando':
      default:
        $cmd = "lando db-import";
        break;
      case 'ddev':
        $db_name = $args[0];
        unset($args[0]);
        $cmd = "ddev import-db --src=$db_name ";
        break;
    }

    $tasks = $this->collectionBuilder($io);
    $tasks->addTask($this->taskExec($cmd)->args($args));

    return $tasks;
  }

}
