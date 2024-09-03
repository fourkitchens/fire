<?php

namespace Fire\Robo\Plugin\Commands;

use Fire\Robo\Plugin\Commands\FireCommandBase;
use Robo\Symfony\ConsoleIO;
use Robo\Robo;

/**
 * Provides a command to Switch between the local_environment.
 */
class EnvSwitchCommand extends FireCommandBase {

  /**
   * Add a new command.
   *
   * Usage Example: fire switch
   *
   * @command env:switch
   * @aliases env-switch, switch, sw
   * @usage fire switch
   */
  public function switch(ConsoleIO $io) {
    $tasks = $this->collectionBuilder($io);
    $env = Robo::config()->get('local_environment');
    $new_env = ($env === 'lando') ? 'ddev' : 'lando';
    $filename = 'fire.local.yml';
    $new_content = "local_environment: $new_env";

    if (file_exists($filename)) {
      $content = file_get_contents($filename);

      if (strpos($content, 'local_environment') !== FALSE) {
        $content = preg_replace('/^local_environment\s*:\s*.*/m', $new_content, $content);
      }
      else {
        $content .= PHP_EOL . $new_content;
      }

      $new_content = $content;
    }

    file_put_contents($filename, $new_content);
    $tasks->addTask($this->taskExec("$env poweroff"));
    $tasks->addTask($this->taskExec("$new_env start"));
    $tasks->addTask($this->taskExec("$new_env drush uli"));

    return $tasks;
  }

}
