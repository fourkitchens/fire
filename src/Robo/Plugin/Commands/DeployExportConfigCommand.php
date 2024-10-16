<?php

namespace Fire\Robo\Plugin\Commands;

use Robo\Robo;
use Robo\Symfony\ConsoleIO;
use Fire\Robo\Plugin\Commands\FireCommandBase;

/**
 * Provides as Command that Exports the config from the Selected remote env.
 */
class DeployExportConfigCommand extends FireCommandBase {

  /**
   * Exports the config from the selected remote enviroment.
   *
   * Usage Example: fire deploy:export-remote-config
   *
   * @command deploy:export-remote-config
   * @aliases dex
   *
   */
  public function exportRemoteConfig(ConsoleIO $io) {
    $remotePlatform = Robo::config()->get('remote_platform');
    $remoteSiteName = Robo::config()->get('remote_sitename');
    $defaultCanonicalEnv = Robo::config()->get('remote_canonical_env');

    $tasks = $this->collectionBuilder($io);
    $confirmation = $io->confirm('You are about to get the lastest Database from a ' . $remotePlatform . ' envoriment and override your local env with, Do you want to continue?');
    if ($confirmation) {
      $remoteEnv = $io->ask("From which enviroment would like to donwload the Database Copy?", $defaultCanonicalEnv);
      if ($remotePlatform == 'pantheon') {
        $tasks->addTask($this->taskExec('terminus backup:create ' . $remoteSiteName . '.' . $remoteEnv . ' --element=db'));
      }
      if ($remotePlatform == 'acquia') {
        // @todo get the right database name ask to user or configure from fire.yml.
        // Or automatically get it from acquia ?
        $acquiaDb = 'my_db';
        //acli api:environments:database-backup-create <environmentId> <databaseName>
        // https://docs.acquia.com/acquia-cloud-platform/add-ons/acquia-cli/commands/api:environments:database-backup-create
        $tasks->addTask($this->task->taskExec('acli api:environments:database-backup-create ' . $remoteSiteName . '.' . $remoteEnv . ' ' . $acquiaDb));
      }
      $tasks->addTask($this->taskExec('fire local:get-db'));
      $tasks->addTask($this->taskExec('fire local:import-db'));
      $tasks->addTask($this->taskExec('fire drush updb -- -y'));
      $tasks->addTask($this->taskExec('fire drush cr -- -y'));
      $tasks->addTask($this->taskExec('fire drush cex -- -y'));
    }
    return $tasks;
  }
}
