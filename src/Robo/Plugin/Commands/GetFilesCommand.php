<?php

namespace Fire\Robo\Plugin\Commands;

use Robo\Symfony\ConsoleIO;
use Robo\Robo;

/**
 * Provides a command to get the files into local.
 */
class GetFilesCommand extends FireCommandBase {

  /**
   * Get files from remote site and put in the local env.
   *
   * Usage Example: fire local:files:get
   *
   * @command local:get-files
   * @aliases get-files, files-get, getfiles, filesget, get_files, files_get, pull-files, pull_files, local:files:get, local:get:files
   * @usage fire local:files:get
   *
   * @param $args drush you would like to execute.
   */
  public function getFiles(ConsoleIO $io, array $args) {
    $cmd = '';
    $remotePlatform = Robo::config()->get('remote_platform');
    $remoteSiteName = Robo::config()->get('remote_sitename');
    $remoteEnv = Robo::config()->get('remote_canonical_env');
    $origFilesFolder = $this->getlocalEnvRoot() . '/reference';
    $destFilesFolder = $this->getDrupalRoot() . '/sites/default/files';
    $tasks = $this->collectionBuilder($io);

    if (!file_exists($origFilesFolder)) {
      $tasks->addTask($this->_mkdir($origFilesFolder));
    }

    switch ($remotePlatform) {
      case 'acquia':
        if ($this->getCliToolStatus('acli')) {
          $cmd = "cd $origFilesFolder";
          $cmd .= " && acli pull:files $remoteSiteName.$remoteEnv default";
          $cmd .= " && cp -a ./docroot/sites/default/files/* $destFilesFolder";
          $cmd .= " && rm -r ./docroot";
          $cmd .= " && cd ../";
        }
        else {
          return 'Acquia CLI is not installed, please install and configure it: https://docs.acquia.com/acquia-cli/install/';
        }
        break;
      case 'pantheon':
      default:
        if ($this->getCliToolStatus('terminus')) {
          $filesName = 'site-files.tar.gz';

          $cmd = "cd $origFilesFolder";
          $cmd .= " && wget `terminus backup:get $remoteSiteName.$remoteEnv --element=files` -O $filesName";
          $cmd .= " && tar xzvf $filesName";
          $cmd .= " && cp -a files_$remoteEnv/* $destFilesFolder";
          $cmd .= " && rm -r files_$remoteEnv";
          $cmd .= " && cd ../";
        }
        else {
          return 'Terminus is not installed, please install and configure it: https://docs.pantheon.io/terminus/install';
        }
        break;
    }
    $tasks->addTask($this->taskExec("$cmd")->args($args));

    return $tasks;
  }

}
