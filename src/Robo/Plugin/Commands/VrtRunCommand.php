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
    $tasks = $this->collectionBuilder($io);
    if ($env == 'lando') {
      $reconfigureTestingUrls = $io->confirm('Do you want to reconfigure your reference and test urls?', true);
      $newReferenceFiles = $io->confirm('Do you want to re-take the reference screenshots?', true);
      if ($reconfigureTestingUrls) {
        $tasks->addTask($this->taskExec('fire vrt:testing-setup'));
      }
      if ($newReferenceFiles) {
        $tasks->addTask($this->taskExec('fire vrt:reference'));
      }
      $landoConfig = Yaml::parse(file_get_contents($this->getLocalEnvRoot() . '/.lando.yml'));

      $tasks->addTask($this->taskExec($env . ' ssh -s backstopserver -c "cd /app/tests/backstop && backstop test --config=/app/tests/backstop/backstop-local.json"'));
      $tasks->addTask($this->taskOpenBrowser('https://' . $landoConfig['name'] . '.lndo.site/backstop/html_report/index.html'));
    }

    return $tasks;
  }
}
