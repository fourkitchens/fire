<?php

namespace Fire\Robo\Plugin\Commands;

use Robo\Symfony\ConsoleIO;

/**
 * Provides to use "composer install" command from fire.
 */
class ComposerInstallCommand extends FireCommandBase {

  /**
   * Command for: composer install.
   *
   * Usage Example: fire composer-install -- --<options>
   *
   * @command composer:install
   * @aliases composer-install, c-install
   */
  public function composer_install(ConsoleIO $io, array $args) {
    $tasks = $this->collectionBuilder($io);
    $env = Robo::config()->get('local_environment');
    $tasks->addTask($this->taskExec("$env composer install")->args($args));

    return $tasks;
  }

}
