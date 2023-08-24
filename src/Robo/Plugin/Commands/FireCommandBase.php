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
    $themePath = $this->getDrupalRoot() . '/themes/custom/';
    $themeNameFromConfig = Robo::config()->get('local_fe_theme_name');
    if (!$themeNameFromConfig) {
      $folders = scandir($themePath);
      foreach ($folders as $folder) {
        if (preg_match('/^(?!\.).*/', $folder)) {
          // Assumming that there only will be one theme.
          return $themePath . $folder;
        }
      }
    }
    return $themePath . $themeNameFromConfig;
  }

  /**
   * Returns the Local envs Root.
   */
  public function getLocalEnvRoot() {
    $localRoot = explode('/', $this->getDrupalRoot());
    array_pop($localRoot);
    $localRoot = implode('/', $localRoot);
    return $localRoot;
  }

  /**
   * Checks if a CLI tool exist.
   *
   * Name in this way because I needed Robo to ignore this function as a command
   * Set and get modules are automatically ignore as posible commands functions.
   *
   * @param string $toolRootCommand
   *   The command you want to check if exist. E.g.: terminus
   */
  public function getCliToolStatus(string $toolRootCommand) {
    $result = $this->taskExec('which')->arg($toolRootCommand)->printOutput(FALSE)->run();
    return $result->wasSuccessful();
  }


}
