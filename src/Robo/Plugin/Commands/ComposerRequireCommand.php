<?php

namespace Fire\Robo\Plugin\Commands;

use Robo\Symfony\ConsoleIO;

/**
 * Provides to use "composer require" command from fire.
 */
class ComposerRequireCommand extends FireCommandBase {

  /**
   * Command for: composer require.
   *
   * Usage Example: fire composer-require -- <namespace>/<package>:<version>
   *
   * @command composer:require
   * @aliases composer-require, c-require
   * 
   * @param $args composer you would like to execute.
   */
  public function composer_require(ConsoleIO $io, array $args) {
    $tasks = $this->collectionBuilder($io);
    $env = Robo::config()->get('local_environment');
    $tasks->addTask($this->taskExec("$env composer require")->args($args));

    return $tasks;
  }

}
