<?php

namespace Fire\Robo\Plugin\Commands;

use Robo\Robo;
use Robo\Symfony\ConsoleIO;

/**
 * Provides a command to overwrite others command.
 */
class CmdAddCommand extends CmdCustomCommand {

  /**
   * Add a new command.
   *
   * Usage Example: fire command:add
   *
   * @command command:add
   * @aliases ca, ac, add, command-add, add-command
   * @usage fire command-add
   */
  public function addCommand(ConsoleIO $io) {
    $env = Robo::config()->get('local_environment');
    $tasks = $this->collectionBuilder($io);
    $namespace = 'FourKitchens\\FireCustom\\';
    $src = 'fire/src/';
    $commandPath = $src . 'Commands';

    // Step 1: Autoload my new commands.
    if (!$this->composerAutoload($tasks, $env, $namespace, $src)) {
      return;
    }

    // Step 2: Create the directory for the new commands.
    $this->createCustomDirectory($commandPath);

    // Step 3: Create a new command from scratch.
    $this->createCustomCommand($io, $namespace, $commandPath);

    return $tasks;
  }

}
