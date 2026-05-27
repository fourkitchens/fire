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
   * @option $tool Choose backstop or playwright (default: backstop).
   *
   */
  public function vrtReference(ConsoleIO $io, array $args, $opts = ['tool' => 'backstop']) {
    $tool = $this->resolveVrtTool($opts, $io);
    if ($tool === 'playwright') {
      return $this->playwrightTaskExec($io, 'test --grep @vrt --update-snapshots')->run();
    }
    return $this->backstopTaskExec($io, 'reference')->run();
  }
}
