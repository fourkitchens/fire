<?php

namespace Fire\Robo\Plugin\Commands;

use Robo\Symfony\ConsoleIO;

/**
 * Provides to use "composer update" command from fire.
 */
class ComposerRequireCommand extends FireCommandBase {

  /**
   * Command for: composer update.
   *
   * Usage Example: fire composer-update -- <namespace>/<package> --<options>
   *
   * @command composer:update
   * @aliases composer-update, c-update
   * 
   * @param $args composer you would like to execute.
   */
  public function composer_update(ConsoleIO $io, array $args) {
    $tasks = $this->collectionBuilder($io);
    $env = Robo::config()->get('local_environment');
    $tasks->addTask($this->taskExec("$env composer update")->args($args));

    return $tasks;
  }

}
