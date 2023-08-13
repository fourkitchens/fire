<?php

namespace Fire\Robo\Plugin\Commands;

use Robo\Symfony\ConsoleIO;
use Fire\Robo\Plugin\Commands\FireCommandBase;

/**
 * Provides a command to build all js dependencies.
 */
class BuildJsCommand extends FireCommandBase {

  /**
   * Builds Project JS Dependencies (Projects Root).
   *
   * Usage Example: fire build-php
   *
   * @command local:build:js
   * @aliases build-js
   *
   */
  public function buildJs(ConsoleIO $io) {
    $root = $this->getDrupalRoot();
    $root = preg_replace('(\/web|\/docroot)', '', $root);
    $tasks = $this->collectionBuilder($io);
    if(file_exists($root . '/.nvmrc') && getenv('NVM_DIR')) {
        $command = 'export NVM_DIR=$HOME/.nvm && source $NVM_DIR/nvm.sh && cd ' . $root . ' && nvm install && npm install && cd -';
        $tasks->addTask($this->taskExec($command)->printOutput(TRUE));
    }
    else {
      $tasks->addTask($this->taskNpmInstall()->dir($root));
    }
    return $tasks;
  }
}
