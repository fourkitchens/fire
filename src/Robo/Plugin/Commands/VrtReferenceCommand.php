<?php

namespace Fire\Robo\Plugin\Commands;

use Robo\Symfony\ConsoleIO;
use Robo\Robo;

/**
 * Provides to generate the reference files for backstop.
 */
class VrtReferenceCommand extends VrtBase {

  /**
   * Takes new reference screeshots from the reference URL.
   *
   * Usage Example: fire vref
   *
   * @command vrt:reference
   * @aliases vref
   *
   */
  public function vrtReference(ConsoleIO $io, array $args) {
    return $this->backstopTaskExec($io, 'reference')->run();
  }
}
