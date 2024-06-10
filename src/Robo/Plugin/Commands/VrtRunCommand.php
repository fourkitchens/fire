<?php

namespace Fire\Robo\Plugin\Commands;

use Robo\Symfony\ConsoleIO;
use Robo\Robo;
use Symfony\Component\Yaml\Yaml;

/**
 * Provides a command to build all php dependencies.
 */
class VrtRunCommand extends FireCommandBase {

  /**
   * Runs your VRT testing over you local env.
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
      $reconfigureTestingUrls = $io->choice('Do you want yo reconfigure your reference and test urls?', ['Yes', 'No']);
      $newReferenceFiles = $io->choice('Do you want to re-take the reference screenshots?', ['Yes', 'No']);
      if (strtolower($reconfigureTestingUrls) === 'yes') {
        $tasks->addTask($this->taskExec('fire vrt:testing-setup'));
      }
      if (strtolower($newReferenceFiles) === 'yes') {
        $tasks->addTask($this->taskExec('fire vrt:reference'));
      }
      $landoConfig = Yaml::parse(file_get_contents($this->getLocalEnvRoot() . '/.lando.yml'));

      $tasks->addTask($this->taskExec($env . ' ssh -s backstopserver -c "cd /app/tests/backstop && backstop test --config=/app/tests/backstop/backstop-local.json"'));
      $tasks->addTask($this->taskOpenBrowser('https://' . $landoConfig['name'] . '.lndo.site/backstop/html_report/index.html'));
    }

    return $tasks;
  }
}
