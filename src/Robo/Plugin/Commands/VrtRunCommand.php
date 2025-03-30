<?php

namespace Fire\Robo\Plugin\Commands;

use Robo\Symfony\ConsoleIO;
use Robo\Robo;
use Symfony\Component\Yaml\Yaml;

/**
 * Provides a command to run the VRT testing.
 */
class VrtRunCommand extends VrtBase {

  /**
   * Runs your VRT testing.
   *
   * Usage Example: fire vrt-run
   *
   * @command vrt:run
   * @aliases vrun
   *
   */
  public function vrtRun(ConsoleIO $io) {
    $env = Robo::config()->get('local_environment');
    $reconfigureTestingUrls = $io->confirm('Do you want to reconfigure your reference and test urls?', TRUE);
    $newReferenceFiles = $io->confirm('Do you want to re-take the reference screenshots?', TRUE);
    if ($reconfigureTestingUrls) {
      $this->taskExec($this->getFireExecutable() . ' vrt:testing-setup')->run();
    }
    if ($newReferenceFiles) {
      $this->taskExec($this->getFireExecutable() . ' vrt:reference')->run();
    }
    
    if ($env === 'lando') {
      $landoConfig = Yaml::parse(file_get_contents($this->getLocalEnvRoot() . '/.lando.yml'));
      $this->backstopTaskExec($io, 'test')->run();
      // Sometimes there can be a slight delay before the files are available in container.
      sleep(1);
      $this->taskOpenBrowser('https://' . $landoConfig['name'] . '.lndo.site/backstop_data/html_report/index.html')->run();
    }
    elseif ($env === 'ddev') {
      $ddevConfig = Yaml::parse(file_get_contents($this->getLocalEnvRoot() . '/.ddev/config.yaml'));
      $this->backstopTaskExec($io, 'test')->run();
      // Sometimes there can be a slight delay before the files are available in container.
      sleep(1);
      $this->taskOpenBrowser('https://' . $ddevConfig['name']. '.ddev.site/backstop_data/html_report/index.html')->run();
    }
  }
  
}
