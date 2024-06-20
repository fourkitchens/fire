<?php

namespace Fire\Robo\Plugin\Commands;

use Robo\Robo;
use Robo\Symfony\ConsoleIO;

/**
 * Provides a command to overwrite others command.
 */
class CmdOverwriteCommand extends CmdCustomCommand {

  /**
   * Overwrite a command.
   *
   * Usage Example: fire command:overwrite
   *
   * @command command:overwrite
   * @aliases co, oc, ow, overwrite, command-overwrite, overwrite-command
   * @usage fire command-overwrite
   */
  public function overwrite(ConsoleIO $io) {
    $env = Robo::config()->get('local_environment');
    $tasks = $this->collectionBuilder($io);
    $namespace = 'FourKitchens\\FireCustom\\';
    $src = 'fire/src/';
    $commandPath = $src . 'Commands/';

    // Step 1: Autoload my new commands.
    if (!$this->composerAutoload($tasks, $env, $namespace, $src)) {
      return;
    }

    // Step 2: Create the directory for the new commands.
    $this->createCustomDirectory($commandPath);

    // Step 3: ask the user for the command they want to override.
    $selectedCommand = $this->askOverwriteCommand();

    // Step 4: Copy the current command to the new path.
    $this->overwriteExistingCommand($io, $namespace, $commandPath, $selectedCommand);

    return $tasks;
  }

}
