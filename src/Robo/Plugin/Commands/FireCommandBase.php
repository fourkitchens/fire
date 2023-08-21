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
   * Returns the Drupals Root path.
   */
  public function getDrupalRoot() {
    return $this->drupalRootPath;
  }

  /**
   * Returns the current's site Themes root folder.
   */
  public function getThemePath() {
    return $this->getDrupalRoot() . '/themes/custom/' . Robo::config()->get('local_fe_theme_name');
  }

  /**
   * Returns the Local envs Root.
   */
  public function getlocalEnvRoot() {
    $localRoot = explode('/', $this->getDrupalRoot());
    array_pop($localRoot);
    $localRoot = implode('/', $localRoot);
    return $localRoot;
  }

  /**
   * Checks if a CLI tool exist.
   */
  public function cliToolExist(string $toolRootCommand) {
    $result = $this->taskExec('which')->arg($toolRootCommand)->printOutput(FALSE)->run();
    return $result->wasSuccessful();
  }


}
