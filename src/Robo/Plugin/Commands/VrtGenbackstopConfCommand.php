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
    $tasks = $this->collectionBuilder($io);

    $assets = dirname(__DIR__, 4) . '/assets/templates/';
    if (!file_exists($this->getLocalEnvRoot() . '/tests/backstop')) {
      $tasks->addTask($this->taskFilesystemStack()->mkdir($this->getLocalEnvRoot() . '/tests/backstop'));
    }
    $override = $io->confirm("This action Will create/override the following files:\n /tests/backstop/backstop.json Do you want to continue?", FALSE);
    if ($override) {
      $tasks->addTask($this->taskFilesystemStack()->copy($assets . 'backstop.json', $this->getLocalEnvRoot() . '/tests/backstop/backstop.json'));
      $tasks->addTask($this->taskFilesystemStack()->copy($assets . 'backstop.json', $this->getLocalEnvRoot() . '/tests/backstop/backstop-local.json'));
    }

    // Adding new lines to .gitignore,
    $tasks->addTask($this->taskWriteToFile($this->getLocalEnvRoot() . '/.gitignore')
      ->textFromFile($this->getLocalEnvRoot() . '/.gitignore')
      ->appendUnlessMatches('/# FIRE VRT testing/', "# FIRE VRT testing\n")
      ->appendUnlessMatches('/tests\/backstop\/backstop-local\.json/', "tests/backstop/backstop-local.json\n")
      ->appendUnlessMatches('/web\/backstop_data\/\*/', "web/backstop_data/*")
    );

    return $tasks;
  }
}
