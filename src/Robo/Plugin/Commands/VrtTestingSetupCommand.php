<?php

namespace Fire\Robo\Plugin\Commands;

use Robo\Symfony\ConsoleIO;
use Robo\Robo;

/**
 * Setups the Testing and reference sites for VRT testing.
 */
class VrtTestingSetupCommand extends FireCommandBase {

  /**
   * Setups the Testing and reference sites for VRT testing.
   *
   * Usage Example: fire vrt:testing-setup
   *
   * @command vrt:testing-setup
   * @aliases vtestingsetup
   *
   */
  public function vrtTestingSetup(ConsoleIO $io) {
    $remotePlatform = Robo::config()->get('remote_platform');
    $remoteSiteName = Robo::config()->get('remote_sitename');
    $remoteCanonicalEnv = Robo::config()->get('remote_canonical_env');

    $tasks = $this->collectionBuilder($io);
    if (strtolower($remotePlatform) === 'pantheon') {

      $testEnviroment = $io->ask("What environment are you testing (typically a multidev or dev)?");
      $testEnviroment = strtolower($testEnviroment);
      $testEnviroment = trim($testEnviroment);

      $canonicalEnvOverride = $io->ask('What is the reference environment (typically dev, test, live)?', $remoteCanonicalEnv);
      $canonicalEnvOverride = strtolower($canonicalEnvOverride);
      $canonicalEnvOverride = trim($canonicalEnvOverride);

      $test_domain = "https://$testEnviroment-$remoteSiteName.pantheonsite.io";
      $reference_domain = "https://$canonicalEnvOverride-$remoteSiteName.pantheonsite.io";

      $cloneReferenceEnv = $io->choice('Do you want to Clone the database and files from ' . $canonicalEnvOverride . ' to your test env ' . $testEnviroment, ['Yes', 'No']);

      $assets = dirname(__DIR__, 4) . '/assets/templates/';
      if (!file_exists($this->getLocalEnvRoot() . '/tests/backstop/backstop.json')) {
        $tasks->addTask($this->taskFilesystemStack()->copy($assets . 'backstop.json', $this->getLocalEnvRoot() . '/tests/backstop/backstop-local.json', TRUE));
      }
      else {
        $tasks-s>addTask($this->taskFilesystemStack()->copy($this->getLocalEnvRoot() . '/tests/backstop/backstop.json', $this->getLocalEnvRoot() . '/tests/backstop/backstop-local.json', TRUE));
      }
      // Replacing Source URL
      $tasks->addTask(
        $this->taskReplaceInFile($this->getLocalEnvRoot() . '/tests/backstop/backstop-local.json')
          ->from('"url": "')
          ->to('"url": "' . $test_domain)
      );
      // Replacing Reference url
      $tasks->addTask(
        $this->taskReplaceInFile($this->getLocalEnvRoot() . '/tests/backstop/backstop-local.json')
        ->from('"referenceUrl": "')
        ->to('"referenceUrl": "' . $reference_domain)
      );

      // If user wants to clone the reference into the test env.
      if (strtolower($cloneReferenceEnv) === 'yes') {
        if ($this->getCliToolStatus('terminus')) {
          $tasks->addTask($this->taskExec("terminus env:clone-content $remoteSiteName.$canonicalEnvOverride $testEnviroment --cc --updatedb -y"));
          $tasks->addTask($this->taskExec("terminus drush $remoteSiteName.$testEnviroment -- cim -y"));
          $tasks->addTask($this->taskExec("terminus drush $remoteSiteName.$testEnviroment -- cr"));
          $tasks->addTask($this->taskExec("terminus drush $remoteSiteName.$testEnviroment -- cim -y"));
        }
      }
    }
    return $tasks;
  }
}
