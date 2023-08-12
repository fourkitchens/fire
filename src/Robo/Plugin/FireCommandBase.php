<?php

namespace Fire\Robo\Plugin\Commands;

use DrupalFinder\DrupalFinder;
use Robo\Tasks;


/**
 * Base class for Other Fire commands.
 */
class FireCommandBase extends Tasks {

  protected $root;
  protected $themePath;

  public public function __construct() {
    $drupalFinder = new DrupalFinder();
    $drupalFinder->locateRoot(getcwd());
    $this->root = $drupalFinder->getDrupalRoot();
  }


}
