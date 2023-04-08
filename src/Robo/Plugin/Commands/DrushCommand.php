<?php

namespace Fire\Robo\Plugin\Commands;

use Robo\Symfony\ConsoleIO;
use Robo\Robo;
use Robo\Tasks;

/**
 * Provides a Drush command proxy.
 */
class DrushCommand extends Tasks {

  /**
   * Drush proxy for local envs.
   *
   * Usage Example: fire drush -- uli
   *
   * @command local:drush
   * @aliases drush
   * @usage -- uli
   *
   * @param $args drush you would like to execute.
   */
  public function drush(ConsoleIO $io, array $args) {
    $env = Robo::config()->get('environment');
    $tasks = $this->collectionBuilder($io);
    $tasks->addTask($this->taskExec($env . ' drush')->args($args));
    return $tasks;
  }
}
