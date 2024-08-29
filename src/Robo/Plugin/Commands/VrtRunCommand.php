<?php

namespace Fire\Robo\Plugin\Commands;

use Robo\Symfony\ConsoleIO;
use Robo\Robo;
use Symfony\Component\Yaml\Yaml;

/**
 * Provides a command to run the VRT testing.
 */
class VrtRunCommand extends FireCommandBase {

  /**
   * Runs your VRT testing (lando Only)
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
      $this->taskExec('fire vrt:testing-setup')->run();
    }
    if ($newReferenceFiles) {
      $this->taskExec('fire vrt:reference')->run();
    }
    if ($env === 'lando') {
      $landoConfig = Yaml::parse(file_get_contents($this->getLocalEnvRoot() . '/.lando.yml'));
      $this->taskExec($env . ' ssh -s backstopserver -c "cd /app/tests/backstop && backstop test --config=/app/tests/backstop/backstop-local.json"')->run();
      $this->taskOpenBrowser('https://' . $landoConfig['name'] . '.lndo.site/backstop/html_report/index.html')->run();
    }
    elseif ($env === 'ddev') {
      $this->taskExec($env . ' backstop test')->run();
      $this->taskOpenBrowser('https://bscr.ddev.site/backstop_data/html_report/index.html')->run();
    }
  }
}
