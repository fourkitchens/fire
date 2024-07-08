<?php

namespace Fire\Robo\Plugin\Commands;

use Robo\Symfony\ConsoleIO;
use Robo\Robo;

/**
 * Provides a command To easylly setup and Run phpcs over your local env.
 */
class LintPhpCommand extends FireCommandBase {

  /**
   * Runs and configure Phpcs for you local env.
   *
   * Usage Example: fire local:lint:php
   *
   * @command local:lint:php
   * @aliases phpcs
   *
   */
  public function phpcsRun(ConsoleIO $io) {
    $composerJson = file_get_contents($this->getLocalEnvRoot() . '/composer.json');
    $composerJson = json_decode($composerJson, TRUE);
    $installComposerPackages = FALSE;
    $createPhpCsFile = FALSE;
    if (!isset($composerJson['require-dev']['drupal/coder'])) {
      $installComposerPackages = $io->confirm("You don't have the Coder composer package installed, Do you want us to install it for you (Required for linting)?", TRUE);
    }
    if (!file_exists($this->getLocalEnvRoot() . '/phpcs.xml')) {
      $createPhpCsFile = $io->confirm("You don't have a Base PHPCS configuration file (phpcs.xml), Do you want us to create one for your Project?", TRUE);
    }
    $tasks = $this->collectionBuilder($io);
    if ($installComposerPackages) {
    $tasks->addTask($this->taskComposerRequire()
      ->dependency('dealerdirect/phpcodesniffer-composer-installer')
      ->dir($this->getLocalEnvRoot())
      ->dev()
      ->ignorePlatformRequirements()
    );
    $tasks->addTask($this->taskComposerRequire()
      ->dependency('drupal/coder')
      ->dir($this->getLocalEnvRoot())
      ->dev()
      ->ignorePlatformRequirements()
    );
    }
    if ($createPhpCsFile) {
      $assets = dirname(__DIR__, 4) . '/assets/templates/';
      $tasks->addTask($this->taskFilesystemStack()->copy($assets . 'phpcs.xml', $this->getLocalEnvRoot() . '/phpcs.xml'));
    }
    $tasks->addTask($this->taskExec('./vendor/bin/phpcs -d memory_limit=-1'));
    return $tasks;
  }
}
