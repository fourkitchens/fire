<?php

namespace Fire\Robo\Plugin\Commands;

use Robo\Symfony\ConsoleIO;
use Fire\Robo\Plugin\Commands\FireCommandBase;
use Robo\Robo;

/**
 * Provides a command to watch the custom theme.
 */
class ThemeWatchCommand extends FireCommandBase {

  /**
   * Builds Projects theme.
   *
   * Usage Example: fire theme-watch
   *
   * @command local:theme:watch
   * @aliases theme-watch, watch-theme, tw
   *
   */
  public function themeWatch(ConsoleIO $io) {
    $env = Robo::config()->get('local_environment');
    $root = $this->getThemePath();
    $tasks = $this->collectionBuilder($io);
    $npmCommand = Robo::config()->get('local_theme_watch_script') ?: '';
    $command = 'cd ' . $root . ' && npm run ' . $npmCommand;

    if (empty($npmCommand)) {
      $io->say('You have not configured any command for "theme watch" please add the variable "local_theme_watch_script" to your fire.yml (or fire.local.yml) configuration file.');
    }
    elseif (file_exists($root . '/.nvmrc') && getenv('NVM_DIR')) {
      $command = 'export NVM_DIR=$HOME/.nvm && . $NVM_DIR/nvm.sh && cd ' . $root . ' && nvm install && npm ci && npm run ' . $npmCommand;
    }

    $tasks->taskExec($command);
    return $tasks;
  }

}
