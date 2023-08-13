<?php

namespace Fire\Robo\Plugin\Commands;

use DrupalFinder\DrupalFinder;
use Robo\Tasks;
use Robo\Robo;

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
    return $this->getDrupalRoot() . '/themes/custom/' . Robo::config()->get('local_fe_theme_name');
  }

}
