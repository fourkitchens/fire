<?php

namespace Fire\Robo\Plugin\Commands;

use Robo\Symfony\ConsoleIO;

/**
 * Provides to use "composer" commands from fire.
 */
class ComposerCommand extends FireCommandBase {

  /**
   * Command tu run composer sentences.
   *
   * Usage Example: fire composer -- install
   *
   * @command local:composer
   * @aliases composer, cp
   * @usage composer -- install
   *
   * @param $args drush you would like to execute.
   */
  public function composer(ConsoleIO $io, array $args) {
    $env = Robo::config()->get('local_environment');
    $tasks = $this->collectionBuilder($io);
    $tasks->addTask($this->taskExec("$env composer")->args($args));

    return $tasks;
  }

}
