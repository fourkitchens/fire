<?php

namespace Fire\Robo\Plugin\Commands;

use Robo\Symfony\ConsoleIO;
use Robo\Robo;
use Symfony\Component\Yaml\Yaml;

/**
 * Provides a command to build all php dependencies.
 */
class VrtLocalEnvConfigureCommand extends FireCommandBase {

  /**
   * Runs your VRT testing over you local env.
   *
   * Usage Example: fire vrt:local-config
   *
   * @command vrt:local-config
   * @aliases vlocalconf
   *
   */
  public function vrtLocalEnvConfigure(ConsoleIO $io) {
    $env = Robo::config()->get('local_environment');
    $tasks = $this->collectionBuilder($io);
    if ($env == 'lando') {
      $landoConfig = Yaml::parse(file_get_contents($this->getLocalEnvRoot() . '/.lando.yml'));
     // var_dump($landoConfig);
      if (!isset($landoConfig['services']['backstopserver'])) {
        $landoConfig['services']['backstopserver'] = [
          'type' => 'node',
          'overrides' => [
            'image' => 'backstopjs/backstopjs:6.3.1',
            'shm_size' => '2gb',
          ],
          'run' => 'rm -rf /app/web/backstop/bitmaps_test/*',
        ];
        $landoYmalDump = Yaml::dump($landoConfig, 5);
        file_put_contents($this->getLocalEnvRoot() . '/.lando.yml', $landoYmalDump);
        $tasks->addTask($this->taskExec('lando rebuild -y'));
      }
    }

    return $tasks;
  }
}
