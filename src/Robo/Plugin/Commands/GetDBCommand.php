<?php

namespace Fire\Robo\Plugin\Commands;

use Robo\Symfony\ConsoleIO;
use Robo\Robo;

/**
 * Provides a command to get the database into local.
 */
class GetDBCommand extends FireCommandBase {

  /**
   * Get the database for local env.
   *
   * Usage Example: fire local:db:get
   *
   * @command local:get-db
   * @aliases get-db, db-get, getdb, dbget, get_db, db_get, local:db:get, local:get:db
   * @usage fire local:db:get
   *
   * @param $args drush you would like to execute.
   */
  public function getDB(ConsoleIO $io, array $args) {
    $cmd = '';
    $remotePlatform = Robo::config()->get('remote_platform');
    $remoteSiteName = Robo::config()->get('remote_sitename');
    $remoteEnv = Robo::config()->get('remote_canonical_env');
    $dbFolder = $this->getLocalEnvRoot() . '/reference';
    $tasks = $this->collectionBuilder($io);

    if (!file_exists($dbFolder)) {
      $tasks->addTask($this->_mkdir($dbFolder));
    }

    switch ($remotePlatform) {
      case 'acquia':
        if ($this->getCliToolStatus('acli')) {
          $cmd = 'wget "' . $this->getAcquiaBackupLink($remoteSiteName, $remoteEnv) . '" -O '. $dbFolder .'/site-db.sql.gz';
        }
        else {
          return 'Acquia CLI is not installed, please install and configure it: https://docs.acquia.com/acquia-cli/install/';
        }
        break;
      case 'pantheon':
      default:
        if ($this->getCliToolStatus('terminus')) {
          $cmd = "wget `terminus backup:get $remoteSiteName.$remoteEnv --element=db` -O ". $dbFolder ."/site-db.sql.gz";
        }
        else {
          return 'Terminus is not installed, please install and configure it: https://docs.pantheon.io/terminus/install';
        }
        break;
    }
    $tasks->addTask($this->taskExec("$cmd")->args($args));

    return $tasks;
  }

  /**
   * Helper function to get the acquias last Backup.
   *
   */
  private function getAcquiaBackupLink($remoteSiteName, $remoteEnv) {
    $backupsList = $this->taskExec('acli api:environments:database-backup-list')->args([$remoteSiteName . '.'. $remoteEnv , $remoteSiteName])->printOutput(false)->run();
    $backupsList = $backupsList->getOutputData();
    $backupsList = json_decode($backupsList);
    if (!isset($backupsList[0])) {
      return "Not available backups to download.";
    }
    $backupId = $backupsList[0]->id;
    $backupInfo = $this->taskExec('acli api:environments:database-backup-download')->args([$remoteSiteName . '.' . $remoteEnv, $remoteSiteName, $backupId])->printOutput(false)->run();
    $backupInfo = $backupInfo->getOutputData();
    $backupInfo = json_decode($backupInfo);

    return $backupInfo->url;
  }

}
