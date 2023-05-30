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
   * @aliases import-db
   * @usage -- <database-file>.sql.gz
   *
   * @param $args drush you would like to execute.
   */
  public function import_db(ConsoleIO $io, array $args) {
    return $this->run($io, $args);
  }

  /**
   * Import database for local envs.
   *
   * Usage Example: fire db-import -- <database-file>.sql.gz
   *
   * @command local:db-import
   * @aliases db-import
   * @usage -- <database-file>.sql.gz
   *
   * @param $args drush you would like to execute.
   */
  public function db_import(ConsoleIO $io, array $args) {
    return $this->run($io, $args);
  }

  /**
   * Run both commands.
   */
  public function run(ConsoleIO $io, array $args) {
    $cmd = ''; 
    $env = Robo::config()->get('environment');

    switch ($env) {
      case 'lando':
      default:
        $cmd = 'db-import'; 
        break;
      case 'ddev':
        $cmd = 'import-db --src='; 
        break;
    }

    $tasks = $this->collectionBuilder($io);
    $tasks->addTask($this->taskExec("$env  $cmd")->args($args));
    return $tasks;
  }
}
