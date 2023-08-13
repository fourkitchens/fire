<?php

namespace Fire\Robo\Plugin\Commands;

use DrupalFinder\DrupalFinder;
use Robo\Tasks;


/**
 * Base class for Other Fire commands.
 */
class FireCommandBase extends Tasks {


  /**
   * The current drupal Root path.
   *
   * @var string
   */
  protected $drupalRootPath;

  /**
   * The current drupal Themes path.
   *
   * @var string
   */
  protected $drupalThemePath;

  /**
   * Builds the FireCommandBase class.
   */
  public function __construct() {
    $drupalFinder = new DrupalFinder();
    $drupalFinder->locateRoot(getcwd());
    $this->drupalRootPath = $drupalFinder->getDrupalRoot();
  }

  /**
   *  Returns the Drupals Root path.
   */
  public function getDrupalRoot() {
    return $this->drupalRootPath;
  }

  public function getThemePath() {
    $pathInfo = $this->taskExec('find /Users/rodrigoespinoza/development/scr/web/themes/custom -maxdepth 1')->printOutput(true)->run();
    echo(serialize($pathInfo));
  }

}
