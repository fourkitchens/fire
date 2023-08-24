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
   * Configures Xdebug for your local env (lando, ddev);
   *
   * Usage Example: fire xdebug-en
   *
   * @command xdebug:enable
   * @aliases xd-en
   *
   */
  public function xdebugEnable(ConsoleIO $io) {
    $env = Robo::config()->get('local_environment');
    $confirmation = $io->ask("Select your Editor:\n- [0] vscode");
    if (preg_match('/^\d{1}$/', $confirmation, $matches)) {
      $tasks = $this->collectionBuilder($io);
      if ($matches[0] == '0') {
        $assets = dirname(__DIR__, 4) . '/assets/xdebug/';
        if ($env == 'lando') {
           $tasks->addTask($this->taskFilesystemStack()->copy($assets . 'vscode/' . $env . '/launch.json', $this->getLocalEnvRoot() . '/.vscode/launch.json'));
           $tasks->addTask($this->taskFilesystemStack()->copy($assets . 'vscode/' . $env . '/php.ini', $this->getLocalEnvRoot() . '/.vscode/php.ini'));
           $tasks->addTask($this->taskFilesystemStack()->copy($assets . 'vscode/' . $env . '/.lando.local.yml', $this->getLocalEnvRoot() . '/.lando.local.yml'));
           $tasks->addTask($this->taskExec('lando rebuild -y'));
        }
      }
      return $tasks;
    }
  }
}
