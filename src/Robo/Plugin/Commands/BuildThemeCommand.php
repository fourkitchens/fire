<?php

namespace Fire\Robo\Plugin\Commands;

use Robo\Symfony\ConsoleIO;
use Fire\Robo\Plugin\Commands\FireCommandBase;
use Robo\Robo;

/**
 * Provides a command to build the custom theme.
 */
class BuildThemeCommand extends FireCommandBase {

  /**
   * Builds Projects theme.
   *
   * Usage Example: fire build-theme
   *
   * @command local:build:theme
   * @aliases build-theme
   *
   */
  public function buildTheme(ConsoleIO $io, $options = ['watch' => FALSE]) {
    $root = $this->getThemePath();
    $tasks = $this->collectionBuilder($io);
    $npmCommand = Robo::config()->get('local_theme_build_script');
    $command = 'cd ' . $root . ' && npm install && npm run ' . $npmCommand;
    if (file_exists($root . '/.nvmrc') && getenv('NVM_DIR')) {
        $command = 'export NVM_DIR=$HOME/.nvm && source $NVM_DIR/nvm.sh && cd ' . $root . ' && nvm install && npm install && npm run ' . $npmCommand;
    }
    if ($options['watch']) {
      $this->taskWatch()->monitor($root, function () {
        $this->taskExec($command)->run();
      })->run();
    }
    else {
      $tasks->taskExec($command);
    }



    return $tasks;
  }
}
