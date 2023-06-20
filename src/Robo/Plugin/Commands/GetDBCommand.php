<?php

namespace Fire\Robo\Plugin\Commands;

use Robo\Symfony\ConsoleIO;
use Robo\Robo;
use Robo\Tasks;

/**
 * Provides a command to get the database into local.
 */
class GetDBCommand extends Tasks {

  /**
   * Import database for local envs.
   *
   * Usage Example: fire local:db:get
   *
   * @command local:import-db
   * @aliases get-db, db-get, getdb, dbget, get_db, db_get, local:db:get, local:get:db
   * @usage fire local:db:get
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
    $platform = Robo::config()->get('platform');
    $sitename = Robo::config()->get('sitename');
    $site_env = Robo::config()->get('siteenv');

    switch ($platform) {
      case 'acquia':
        $today = date('Y-m-d');
        $server = Robo::config()->get('server');
        $backupname = Robo::config()->get('backupname');
        $cmd = "scp -r  $sitename.$site_env@$server.prod.hosting.acquia.com:/mnt/files/$sitename/backups/$site_env-$sitename-$backupname-$today.sql.gz `pwd`/site-db.sql.gz";
        break;
      case 'pantheon':
      default:
        $cmd = "wget `terminus backup:get $sitename.$site_env --element=db` -O site-db.sql.gz";
        break;
    }

    $tasks = $this->collectionBuilder($io);
    $tasks->addTask($this->taskExec("$cmd")->args($args));

    return $tasks;
  }

}
