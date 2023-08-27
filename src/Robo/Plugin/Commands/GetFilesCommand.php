<?php

namespace Fire\Robo\Plugin\Commands;

use Robo\Symfony\ConsoleIO;
use Robo\Robo;

/**
 * Provides a command to get the files into local.
 */
class GetFilesCommand extends FireCommandBase {

  /**
   * Import database for local envs.
   *
   * Usage Example: fire local:files:get
   *
   * @command local:get-files
   * @aliases get-files, files-get, getfiles, filesget, get_files, files_get, pull-files, pull_files, local:file:get, local:get:files
   * @usage fire local:files:get
   * @option $no-download Reuse your existing files copy in the reference folder and placing them in the files folder (Pantheon only).
   */
  public function getFiles(ConsoleIO $io, $opts = ['no-download' => FALSE]) {
    $tasks = $this->collectionBuilder($io);
    $remotePlatform = Robo::config()->get('remote_platform');
    $remoteSiteName = Robo::config()->get('remote_sitename');
    $remoteEnv = Robo::config()->get('remote_canonical_env');
    $origFilesFolder = $this->getlocalEnvRoot() . '/reference';
    $destFilesFolder = $this->getDrupalRoot() . '/sites/default/files';

    // Create the folder.
    if (!file_exists($origFilesFolder)) {
      $tasks->addTask($this->taskFilesystemStack()->mkdir($origFilesFolder));
    }

    // Creating files the folder if not exists.
    if (!file_exists($destFilesFolder)) {
      $this->taskFilesystemStack()->mkdir($destFilesFolder);
    }
    else {
      // Removing all existing files from files folder.
      if ($remotePlatform == 'pantheon') {
        $tasks->addTask($this->taskCleanDir($destFilesFolder));
      }
    }

    switch ($remotePlatform) {
      case 'acquia':
        $tasks = $this->getFilesAcquia($io, $tasks, $remoteSiteName, $remoteEnv);
        break;
      case 'pantheon':
      default:
        $tasks = $this->getFilesPantheon($io, $opts, $tasks, $remoteSiteName, $remoteEnv, $origFilesFolder, $destFilesFolder);
        break;
    }

    return $tasks;
  }

  /**
   * Helper function to get files from Acquia.
   */
  private function getFilesAcquia(ConsoleIO $io, $tasks, $remoteSiteName, $remoteEnv) {
    /**
     * Acquia CLI will automatically placed all files under docroot/sites/default/files.`
     */
    if (file_exists($this->getLocalEnvRoot() . '/docroot/sites/default/files')) {
      if ($this->getCliToolStatus('acli')) {
          $io->say('Syncing files from the ' . $remoteEnv . ' environment...');
          $cmd = 'cd ' . $this->getLocalEnvRoot();
          $cmd .= ' && acli pull:files ' . $remoteSiteName . '.' . $remoteEnv . ' default';
          $tasks->addTask($this->taskExec($cmd));
      }
      else {
        return 'Acquia CLI is not installed, please install and configure it: https://docs.acquia.com/acquia-cli/install/';
      }
    }
    else {
      return 'Your Local env doesnt have a valid acquia project structure, your drupal root should be the "docroot" folder.';
    }
    return $tasks;
  }

  /**
   * Helper function to get files from Pantheon.
   */
  private function getFilesPantheon(ConsoleIO $io, array $opts, $tasks, $remoteSiteName, $remoteEnv, $origFilesFolder, $destFilesFolder) {

    if ($this->getCliToolStatus('terminus')) {
      $filesName = 'site-files.tar.gz';
      if (!$opts['no-download']) {
        $cmd = "wget `terminus backup:get $remoteSiteName.$remoteEnv --element=files` -O $origFilesFolder/$filesName";
        $tasks->addTask($this->taskExec($cmd));
      }
      $tasks->addTask($this->taskFilesystemStack()->mkdir($origFilesFolder . '/files_' . $remoteEnv));
      $tasks->addTask($this->taskExec('tar xzvf ' . $origFilesFolder . '/' . $filesName . ' -C ' . $origFilesFolder . '/files_' . $remoteEnv));
      $tasks->addTask($this->taskCopyDir([$origFilesFolder . '/files_' . $remoteEnv => $destFilesFolder]));
      $tasks->addTask($this->taskDeleteDir($origFilesFolder . '/files_' . $remoteEnv));
    }
    else {
      return 'Terminus is not installed, please install and configure it: https://docs.pantheon.io/terminus/install';
    }

    return $tasks;
  }

}
