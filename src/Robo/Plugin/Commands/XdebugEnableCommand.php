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
    $confirmation = $io->ask("Select your Editor:\n- [0] vscode");
    if (preg_match('/^\d{1}$/', $confirmation, $matches)) {
      $tasks = $this->collectionBuilder($io);
      $assets = dirname(__DIR__, 4) . '/assets/xdebug/';

      // VScode.
      if ($matches[0] == '0') {
        if (!file_exists($this->getLocalEnvRoot() . '/.vscode')) {
          $tasks->addTask($this->taskFilesystemStack()->mkdir($this->getLocalEnvRoot() . '/.vscode'));
        }

        if ($env == 'lando') {
           $override = $io->ask("This action Will create/override the following files:\n.vscode/launch.json\n.vscode/php.ini\n.lando.local.yml\n Do you want to continue? (Y|N)");
           if (preg_match('/^[Yy]{1}$/', $override, $matches)) {
            $tasks->addTask($this->taskFilesystemStack()->copy($assets . 'vscode/' . $env . '/launch.json', $this->getLocalEnvRoot() . '/.vscode/launch.json'));
            $tasks->addTask($this->taskFilesystemStack()->copy($assets . 'vscode/' . $env . '/php.ini', $this->getLocalEnvRoot() . '/.vscode/php.ini'));
            $tasks->addTask($this->taskFilesystemStack()->copy($assets . 'vscode/' . $env . '/.lando.local.yml', $this->getLocalEnvRoot() . '/.lando.local.yml'));
            $tasks->addTask($this->taskExec('lando rebuild -y'));
          }
        }
        elseif ($env == 'ddev') {
          $override = $io->ask("This action Will create/override the following files:\n.vscode/launch.json\n Do you want to continue? (Y|N)");
          if (preg_match('/^[Yy]{1}$/', $override, $matches)) {
            $tasks->addTask($this->taskFilesystemStack()->copy($assets . 'vscode/' . $env . '/launch.json', $this->getLocalEnvRoot() . '/.vscode/launch.json'));
            $tasks->addTask($this->taskExec('ddev xdebug enable'));
          }
        }
      }
      return $tasks;
    }
  }
}
