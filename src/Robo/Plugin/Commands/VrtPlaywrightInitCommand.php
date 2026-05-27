<?php

namespace Fire\Robo\Plugin\Commands;

use Robo\Exception\AbortTasksException;
use Robo\Symfony\ConsoleIO;
use Robo\Robo;
use Symfony\Component\Yaml\Yaml;

/**
 * Provides a command to initialize Playwright VRT.
 */
class VrtPlaywrightInitCommand extends FireCommandBase {

  /**
   * Configure Playwright VRT scaffolding using ATK.
   *
   * Usage Example: fire vrt:playwright:init
   *
   * @command vrt:playwright:init
   * @aliases vpinit
   * @option $y Run the command with no interection required.
   */
  public function vrtPlaywrightInit(ConsoleIO $io, $opts = ['y|y' => FALSE]) {
    $projectRoot = $this->getLocalEnvRoot();
    $drupalRoot = $this->getDrupalRoot();
    $atkHome = getenv('ATK_HOME');
    if (!$atkHome) {
      throw new AbortTasksException('ATK_HOME is not set in your environment. Add `export ATK_HOME=./tests/playwright` to your shell profile (PATH) and rerun.');
    }
    $testsRoot = $this->resolveAtkHomePath($projectRoot, $atkHome);

    $shouldProceed = TRUE;
    if (!$opts['y']) {
      $shouldProceed = $io->confirm("This action will generate/update Playwright VRT scaffolding in $testsRoot. Continue?", TRUE);
    }

    if (!$shouldProceed) {
      $io->warning('Playwright VRT init skipped.');
      return 0;
    }

    $tasks = $this->collectionBuilder($io);

    $tasks->addTask($this->taskExec("composer require 'drupal/automated_testing_kit'")->dir($projectRoot));

    $atkSetup = $drupalRoot . '/modules/contrib/automated_testing_kit/module_support/atk_setup';
    if (!file_exists($atkSetup)) {
      throw new AbortTasksException("Automated Testing Kit not found at $atkSetup. Install drupal/automated_testing_kit and rerun.");
    }

    $atkCommand = 'ATK_HOME=' . $atkHome . ' ' . $atkSetup . ' playwright';
    $tasks->addTask($this->taskExec($atkCommand)->dir($projectRoot));
    $tasks->addTask($this->taskExec($atkCommand)->dir($projectRoot));

    $testsDir = $testsRoot . '/tests';
    if (is_dir($testsDir)) {
      $testItems = glob($testsDir . '/*') ?: [];
      foreach ($testItems as $testItem) {
        if (is_dir($testItem)) {
          $folderName = basename($testItem);
          if (in_array($folderName, ['support', 'data', 'vrt'], TRUE)) {
            continue;
          }
        }
        $tasks->addTask($this->taskFilesystemStack()->remove($testItem));
      }
    }

    if (!is_dir($testsRoot . '/tests/vrt')) {
      $tasks->addTask($this->taskFilesystemStack()->mkdir($testsRoot . '/tests/vrt'));
    }
    if (!is_dir($testsRoot . '/tests/support')) {
      $tasks->addTask($this->taskFilesystemStack()->mkdir($testsRoot . '/tests/support'));
    }
    if (!is_dir($testsRoot . '/css')) {
      $tasks->addTask($this->taskFilesystemStack()->mkdir($testsRoot . '/css'));
    }

    $assets = dirname(__DIR__, 4) . '/assets/templates/playwright/';
    $tasks->addTask($this->taskFilesystemStack()->copy($assets . 'playwright.config.js', $testsRoot . '/playwright.config.js', TRUE));
    $tasks->addTask($this->taskFilesystemStack()->copy($assets . 'playwright.atk.config.js', $testsRoot . '/playwright.atk.config.js', TRUE));
    $tasks->addTask($this->taskFilesystemStack()->copy($assets . 'css/screenshotGlobalStyle.css', $testsRoot . '/css/screenshotGlobalStyle.css', TRUE));
    $tasks->addTask($this->taskFilesystemStack()->copy($assets . 'tests/support/aft_utilities.js', $testsRoot . '/tests/support/aft_utilities.js', TRUE));
    $tasks->addTask($this->taskFilesystemStack()->copy($assets . 'tests/vrt/homepage.spec.js', $testsRoot . '/tests/vrt/homepage.spec.js', TRUE));
    $tasks->addTask($this->taskFilesystemStack()->copy($assets . 'tests/vrt/page_components.spec.js', $testsRoot . '/tests/vrt/page_components.spec.js', TRUE));

    $gitignorePath = $testsRoot . '/.gitignore';
    $gitignoreTask = $this->taskWriteToFile($gitignorePath);
    if (file_exists($gitignorePath)) {
      $gitignoreTask->textFromFile($gitignorePath);
    }
    $gitignoreTask
      ->appendUnlessMatches('/node_modules\//', "node_modules/\n")
      ->appendUnlessMatches('/\/test-results\//', "/test-results/\n")
      ->appendUnlessMatches('/\/playwright-report\//', "/playwright-report/\n")
      ->appendUnlessMatches('/\/blob-report\//', "/blob-report/\n")
      ->appendUnlessMatches('/\/cache\//', "/cache/\n")
      ->appendUnlessMatches('/\/\.auth\//', "/.auth/\n")
      ->appendUnlessMatches('/\/tests\/support\/loginAuth\.json/', "/tests/support/loginAuth.json\n")
      ->appendUnlessMatches('/\.env/', ".env\n")
      ->appendUnlessMatches('/\*\.spec\.js-snapshots/', "*.spec.js-snapshots\n");
    $tasks->addTask($gitignoreTask);

    $defaultBaseUrl = $this->getDefaultBaseUrl($projectRoot);
    $tasks->addTask(
      $this->taskReplaceInFile($testsRoot . '/playwright.config.js')
        ->from('__DEFAULT_BASE_URL__')
        ->to($defaultBaseUrl)
    );

    $drushCmd = $this->getDefaultDrushCommand();
    $pantheonSite = Robo::config()->get('remote_sitename') ?: 'remote-pantheon-site-machine-name';
    $tasks->addTask(
      $this->taskReplaceInFile($testsRoot . '/playwright.atk.config.js')
        ->from('__DRUSH_CMD__')
        ->to($drushCmd)
    );
    $tasks->addTask(
      $this->taskReplaceInFile($testsRoot . '/playwright.atk.config.js')
        ->from('__PANTHEON_SITE__')
        ->to($pantheonSite)
    );

    return $tasks;
  }

  /**
   * Resolve ATK_HOME into an absolute path.
   */
  private function resolveAtkHomePath(string $projectRoot, string $atkHome) {
    $atkHome = trim($atkHome);
    if ($atkHome === '') {
      return $projectRoot . '/tests/playwright';
    }
    if (strpos($atkHome, '/') === 0) {
      return rtrim($atkHome, '/');
    }
    $relative = ltrim($atkHome, './');
    return rtrim($projectRoot, '/') . '/' . $relative;
  }

  /**
   * Infer the best default base URL from local env config.
   */
  private function getDefaultBaseUrl(string $projectRoot) {
    $defaultBaseUrl = 'http://mysite.site';
    $env = Robo::config()->get('local_environment');

    if ($env === 'lando' && file_exists($projectRoot . '/.lando.yml')) {
      $landoConfig = Yaml::parse(file_get_contents($projectRoot . '/.lando.yml'));
      if (isset($landoConfig['name'])) {
        return 'https://' . $landoConfig['name'] . '.lndo.site';
      }
    }

    if ($env === 'ddev' && file_exists($projectRoot . '/.ddev/config.yaml')) {
      $ddevConfig = Yaml::parse(file_get_contents($projectRoot . '/.ddev/config.yaml'));
      if (isset($ddevConfig['name'])) {
        return 'https://' . $ddevConfig['name'] . '.ddev.site';
      }
    }

    return $defaultBaseUrl;
  }

  /**
   * Determine the local drush command to use.
   */
  private function getDefaultDrushCommand() {
    $env = Robo::config()->get('local_environment');
    if ($env === 'lando') {
      return 'lando drush';
    }
    if ($env === 'ddev') {
      return 'ddev drush';
    }
    return 'drush';
  }

}
