<?php

namespace FourKitchens\FireCustom\Commands;

use Fire\Robo\Plugin\Commands\FireCommandBase;
use Robo\Robo;

/**
 * Provides a way to use "NPM" commands from fire.
 */
class NpmCommand extends FireCommandBase {

  /**
   * NPM proxy for local envs.
   *
   * Usage Example: fire npm install
   *
   * @command local:npm
   * @aliases npm
   * @usage npm <options>
   *
   * @param $args The npm command you would like to execute.
   */
  public function npm(array $args) {
    $env = Robo::config()->get('local_environment');
    $this->taskExec("$env npm")->args($args)->run();
  }

}
