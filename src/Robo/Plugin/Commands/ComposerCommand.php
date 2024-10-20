<?php

namespace Fire\Robo\Plugin\Commands;

use Robo\Symfony\ConsoleIO;
use Robo\Robo;

/**
 * Provides a way to use "composer" commands from fire.
 */
class ComposerCommand extends FireCommandBase {

  /**
   * Composer proxy for local envs.
   *
   * Usage Example: fire composer update drupal/core -- -W
   *
   * @command local:composer
   * @aliases c, composer
   * @usage composer <options>
   *
   * @param $args The composer command you would like to execute.
   */
  public function composer(ConsoleIO $io, array $args) {
    $env = Robo::config()->get('local_environment');
    $this->taskExec("$env composer")->args($args)->run();
  }

}
