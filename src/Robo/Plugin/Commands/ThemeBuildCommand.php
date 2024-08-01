<?php

namespace Fire\Robo\Plugin\Commands;

use Robo\Symfony\ConsoleIO;
use Fire\Robo\Plugin\Commands\FireCommandBase;
use Robo\Robo;

/**
 * Provides a command to build the custom theme.
 */
class ThemeBuildCommand extends FireCommandBase {

  /**
   * Builds Projects theme.
   *
   * Usage Example: fire theme-build
   *
   * @command local:theme:build
   * @aliases theme-build, build-theme, tb
   *
   */
  public function themeBuild(ConsoleIO $io) {
    $root = $this->getThemePath();
    $tasks = $this->collectionBuilder($io);
    $npmCommand = Robo::config()->get('local_theme_build_script');
    $command = 'cd ' . $root . ' && npm ci && npm run ' . $npmCommand;
    if (file_exists($root . '/.nvmrc') && getenv('NVM_DIR')) {
      $command = 'export NVM_DIR=$HOME/.nvm && . $NVM_DIR/nvm.sh && cd ' . $root . ' && nvm install && npm ci && npm run ' . $npmCommand;
    }
    $tasks->taskExec($command);
    return $tasks;
  }

}
