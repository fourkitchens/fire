<?php

namespace Fire\Robo\Plugin\Commands;

use Robo\Symfony\ConsoleIO;

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
   * @option $tool Choose auto, backstop, or playwright (default: auto).
   *
   */
  public function vrtReference(ConsoleIO $io, array $args, $opts = ['tool' => 'auto']) {
    $tool = $this->resolveVrtTool($opts, $io);
    if ($tool === 'playwright') {
      return $this->taskExec('npm run vrt')
        ->dir($this->getLocalEnvRoot() . '/tests/playwright')
        ->run();
    }
    return $this->backstopTaskExec($io, 'reference')->run();
  }
}
