<?php

namespace Fire\Robo\Plugin\Commands;

use Robo\Symfony\ConsoleIO;
use Robo\Robo;

/**
 * Provides a command to generate the Backstop initial files.
 */
class VrtGenbackstopConfCommand extends FireCommandBase {

  /**
   * Creates a basic Backstop.json for you.
   *
   * Usage Example: fire vrt:generate-backstop-config
   *
   * @command vrt:generate-backstop-config
   * @aliases vgc
   *
   */
  public function vrtGenBackstopConf(ConsoleIO $io) {
    $env = Robo::config()->get('local_environment');
    $tasks = $this->collectionBuilder($io);
    $assets = dirname(__DIR__, 4) . '/assets/templates/';
    if (!file_exists($this->getLocalEnvRoot() . '/tests/backstop')) {
      $tasks->addTask($this->taskFilesystemStack()->mkdir($this->getLocalEnvRoot() . '/tests/backstop'));
    }
    if ($env == 'lando') {
      $override = $io->ask("This action Will create/override the following files:\n /tests/backstop/backstop.json Do you want to continue? (Y|N)");
      if (preg_match('/^[Yy]{1}$/', $override, $matches)) {
        $tasks->addTask($this->taskFilesystemStack()->copy($assets . 'backstop.json', $this->getLocalEnvRoot() . '/tests/backstop/backstop.json'));
        $tasks->addTask($this->taskFilesystemStack()->copy($assets . 'backstop.json', $this->getLocalEnvRoot() . '/tests/backstop/backstop-local.json'));
      }
    }

    return $tasks;
  }
}
