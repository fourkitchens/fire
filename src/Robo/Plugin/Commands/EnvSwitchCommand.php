<?php

namespace Fire\Robo\Plugin\Commands;

use Fire\Robo\Plugin\Commands\FireCommandBase;
use Robo\Symfony\ConsoleIO;
use Robo\Robo;
use Symfony\Component\Yaml\Yaml;

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
    $projectRoot = $this->getLocalEnvRoot();
    $env = Robo::config()->get('local_environment');
    if (file_exists($projectRoot . '/.lando.yml') && file_exists($projectRoot . '/.ddev/config.yaml')) {
      $newEnv = ($env === 'lando') ? 'ddev' : 'lando';
      $confirm = $io->confirm('Do you want to switch from ' . $env  . ' to ' . $newEnv . '?');
      if ($confirm) {
      $tasks = $this->collectionBuilder($io);
      $projectRoot = $this->getLocalEnvRoot();
      $fireFile = "$projectRoot/fire.local.yml";

      // Load the file and update the env.
      $fireLocalConfig = file_exists($fireFile) ? Yaml::parseFile($fireFile) : [];
      $fireLocalConfig['local_environment'] = $newEnv;
      $fireYamlDump = Yaml::dump($fireLocalConfig, 5, 2);
      file_put_contents($fireFile, $fireYamlDump);

      // Stop the old env and start the new one.
      $tasks->addTask($this->taskExec("$env poweroff"));
      $tasks->addTask($this->taskExec("$newEnv start"));
      $tasks->addTask($this->taskExec("$newEnv drush uli"));
      return $tasks;
      }
    }
    else {
      $io->say('Sorry you only have ' . $env . ' configured for your project, so we can not switch.');
    }
  }

}
