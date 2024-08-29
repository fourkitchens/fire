<?php

namespace Fire\Robo\Plugin\Commands;

use Robo\Symfony\ConsoleIO;
use Robo\Robo;
use Symfony\Component\Yaml\Yaml;

/**
 * Provides a command to Alters your Local env so you can use VRT.
 */
class VrtLocalEnvConfigureCommand extends FireCommandBase {

  /**
   * Alters your local enviroment so you can use backstop.
   *
   * Usage Example: fire vrt:local-env-config
   *
   * @command vrt:local-env-config
   * @aliases vlec
   *
   */
  public function vrtLocalEnvConfigure(ConsoleIO $io) {
    $env = Robo::config()->get('local_environment');
    if ($env === 'lando') {
      $landoConfig = Yaml::parse(file_get_contents($this->getLocalEnvRoot() . '/.lando.yml'));
      if (!isset($landoConfig['services']['backstopserver'])) {
        $landoConfig['services']['backstopserver'] = [
          'type' => 'node',
          'overrides' => [
            'image' => 'backstopjs/backstopjs:6.3.23',
            'shm_size' => '2gb',
          ],
          'run' => 'rm -rf /app/web/backstop_data/bitmaps_test/*',
        ];
        $landoYamlDump = Yaml::dump($landoConfig, 5, 2);
        file_put_contents($this->getLocalEnvRoot() . '/.lando.yml', $landoYamlDump);
        $this->taskExec('lando rebuild -y')->run();
        $this->taskExec('lando ssh -s backstopserver -c "cd /app/web/ && backstop init"')->run();
      }
    }
    elseif ($env === 'ddev') {
      $this->taskExec('ddev get fourkitchens/ddev-drupal-backstop')->run();
      $this->taskExec('ddev restart')->run();
      $this->taskExec('ddev backstop init')->run();

    }
  }
}
