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
    $overrideConfig = TRUE;
    if (file_exists($this->getDrupalRoot() . '/backstop_data')) {
      $overrideConfig = $io->confirm('You already have a existing backstop_data folder in: ' . $this->getDrupalRoot() . ' , Do you want to override the configuration using the default backstop files? (This step is only normally needed when doing the initial VRT setup)', FALSE);
    }

    if ($env === 'lando') {
      $landoConfig = Yaml::parse(file_get_contents($this->getLocalEnvRoot() . '/.lando.yml'));
      $landoConfig['services']['backstopserver'] = [
        'type' => 'compose',
        'app_mount' =>'delegated',
        'services' => [
          'image' => 'backstopjs/backstopjs:6.3.23',
          'command' => 'top -b',
          'shm_size' => '2gb',
        ],
      ];
      $landoYamlDump = Yaml::dump($landoConfig, 5, 2);
      file_put_contents($this->getLocalEnvRoot() . '/.lando.yml', $landoYamlDump);
      $this->taskExec('lando rebuild -y')->run();

     if ($overrideConfig) {
      $this->taskExec('lando ssh -s backstopserver -c "cd /app/web/ && backstop init"')->run();
     }
    }
    elseif ($env === 'ddev') {
      $this->taskExec('ddev get fourkitchens/ddev-drupal-backstop')->run();
      $this->taskExec('ddev restart')->run();
      if($overrideConfig) {
        $this->taskExec('ddev backstop init')->run();
      }
    }
  }
}
