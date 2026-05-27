<?php

namespace Fire\Robo\Plugin\Commands;

use Robo\Symfony\ConsoleIO;

/**
 * Provides a command to initialize VRT.
 */
class VrtinitCommand extends VrtBase {

  /**
   * Configure your local enviroment from scratch to use VRT testing.
   *
   * Usage Example: fire vrt:init
   *
   * @command vrt:init
   * @aliases vinit
   * @option $tool Choose backstop or playwright (default: backstop).
   * @option $y Run the command with no interection required.
   *
   */
  public function vrtInit(ConsoleIO $io) {
    $tool = $io->choice("Select the vrt tool:", ['backstopjs(deprecated)', 'playwright']);
    $tasks = $this->collectionBuilder($io);
     if ($tool === 'backstopjs(deprecated)') {
      $tasks->addTask($this->taskExec($this->getFireExecutable() . ' vrt:generate-backstop-config'));
      $tasks->addTask($this->taskExec($this->getFireExecutable() . ' vrt:local-env-config'));
    }
    if ($tool == 'playwright') {
      $tasks->addTask($this->taskExec($this->getFireExecutable() . ' vrt:playwright:init'));
    }
    return $tasks;
  }
}
