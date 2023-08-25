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
   * @option $no-overwrite If the value is TRUE, checks if the files exists and they are no downloaded again.
   */
  public function getFiles(ConsoleIO $io, $opts = ['no-overwrite' => FALSE]) {
    $remotePlatform = Robo::config()->get('remote_platform');
    $remoteSiteName = Robo::config()->get('remote_sitename');
    $remoteEnv = Robo::config()->get('remote_canonical_env');
    $origFilesFolder = $this->getlocalEnvRoot() . '/reference';
    $destFilesFolder = $this->getDrupalRoot() . '/sites/default/files';

    // Create the folder.
    if (!file_exists($origFilesFolder)) {
      $this->_mkdir($origFilesFolder);
    }

    switch ($remotePlatform) {
      case 'acquia':
        $tasks = $this->getFilesAcquia($io, $opts, $remotePlatform, $remoteSiteName, $remoteEnv, $origFilesFolder, $destFilesFolder);
        break;
      case 'pantheon':
      default:
        $tasks = $this->getFilesPantheon($io, $opts, $remotePlatform, $remoteSiteName, $remoteEnv, $origFilesFolder, $destFilesFolder);
        break;
    }

    return $tasks;
  }

  /**
   * Helper function to get files from Acquia.
   */
  private function getFilesAcquia(ConsoleIO $io, array $opts, $remotePlatform, $remoteSiteName, $remoteEnv, $origFilesFolder, $destFilesFolder) {
    $tasks = $this->collectionBuilder($io);

    if ($this->getCliToolStatus('acli')) {
      $uploadFolder = 'docroot';
      if (!$opts['no-overwrite']) {
        $cmd = "cd $origFilesFolder";
        $cmd .= " && acli pull:files $remoteSiteName.$remoteEnv default";
        $cmd .= " && tar czvf $uploadFolder.tar.gz $uploadFolder";
        $this->taskExec($cmd)->run();
      }

      if (file_exists("$origFilesFolder/$uploadFolder.tar.gz")) {
        if (!file_exists("$origFilesFolder/$uploadFolder")) {
          $this->taskExec("cd $origFilesFolder && tar xzvf $uploadFolder.tar.gz")->run();
        }

        $tasks->addTask($this->taskExec("cp -a $origFilesFolder/$uploadFolder/sites/default/files/* $destFilesFolder"));
        $tasks->addTask($this->taskExec("chmod -R 775 $origFilesFolder/$uploadFolder"));
        $tasks->addTask($this->taskExec("rm -r $origFilesFolder/$uploadFolder"));
      }
      else {
        return "The folder '$uploadFolder' doesn't exist.";
      }
    }
    else {
      return 'Acquia CLI is not installed, please install and configure it: https://docs.acquia.com/acquia-cli/install/';
    }

    return $tasks;
  }

  /**
   * Helper function to get files from Pantheon.
   */
  private function getFilesPantheon(ConsoleIO $io, array $opts, $remotePlatform, $remoteSiteName, $remoteEnv, $origFilesFolder, $destFilesFolder) {
    $tasks = $this->collectionBuilder($io);

    if ($this->getCliToolStatus('terminus')) {
      $filesName = 'site-files.tar.gz';
      if (!$opts['no-overwrite']) {
        $cmd = "wget `terminus backup:get $remoteSiteName.$remoteEnv --element=files` -O $origFilesFolder/$filesName";
        $this->taskExec($cmd)->run();
      }

      if (file_exists("$origFilesFolder/$filesName")) {
        $tasks->addTask($this->taskExec("cd $origFilesFolder && tar xzvf $filesName"));
        $tasks->addTask($this->taskExec("cp -a $origFilesFolder/files_$remoteEnv/* $destFilesFolder"));
        $tasks->addTask($this->taskExec("rm -r $origFilesFolder/files_$remoteEnv"));
      }
      else {
        return "The file '$filesName' doesn't exist.";
      }
    }
    else {
      return 'Terminus is not installed, please install and configure it: https://docs.pantheon.io/terminus/install';
    }

    return $tasks;
  }

}
