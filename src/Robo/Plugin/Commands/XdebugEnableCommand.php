<?php

namespace Fire\Robo\Plugin\Commands;

use Robo\Symfony\ConsoleIO;
use Fire\Robo\Plugin\Commands\FireCommandBase;
use Robo\Robo;

/**
 * Provides a command to setup xdebug for your local env.
 */
class XdebugEnableCommand extends FireCommandBase {

  /**
   * Configures your local envs Xdebug to work with your prefered Code editor.
   *
   * Usage Example: fire xdebug-en
   *
   * @command xdebug:enable
   * @aliases xd-en
   *
   */
  public function xdebugEnable(ConsoleIO $io) {
    $env = Robo::config()->get('local_environment');
    $editor = $io->choice("Select your Editor:", ['vscode']);
    $tasks = $this->collectionBuilder($io);
    $assets = dirname(__DIR__, 4) . '/assets/xdebug/';
    echo($editor);
    // VScode.
    if ($editor === 'vscode') {
      if (!file_exists($this->getLocalEnvRoot() . '/.vscode')) {
        $tasks->addTask($this->taskFilesystemStack()->mkdir($this->getLocalEnvRoot() . '/.vscode'));
      }

      if ($env === 'lando') {
          $override = $io->confirm("This action Will create/override the following files:\n.vscode/launch.json\n.vscode/php.ini\n.lando.local.yml\n Do you want to continue?", TRUE);
          if ($override) {
          $tasks->addTask($this->taskFilesystemStack()->copy($assets . 'vscode/' . $env . '/launch.json', $this->getLocalEnvRoot() . '/.vscode/launch.json'));
          $tasks->addTask($this->taskFilesystemStack()->copy($assets . 'vscode/' . $env . '/php.ini', $this->getLocalEnvRoot() . '/.vscode/php.ini'));
          $tasks->addTask($this->taskFilesystemStack()->copy($assets . 'vscode/' . $env . '/.lando.local.yml', $this->getLocalEnvRoot() . '/.lando.local.yml'));
          $tasks->addTask($this->taskExec('lando rebuild -y'));
        }
      }
      elseif ($env === 'ddev') {
        $override = $io->confirm("This action Will create/override the following files:\n.vscode/launch.json\n Do you want to continue?", TRUE);
        if ($override) {
          $tasks->addTask($this->taskFilesystemStack()->copy($assets . 'vscode/' . $env . '/launch.json', $this->getLocalEnvRoot() . '/.vscode/launch.json'));
          $tasks->addTask($this->taskExec('ddev xdebug enable'));
        }
      }
    }
    return $tasks;

  }
}
