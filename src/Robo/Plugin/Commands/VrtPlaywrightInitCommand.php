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
   * @aliases vpinit,vrt:init,vinit
   * @option $y Run the command with no interection required.
   */
  public function vrtPlaywrightInit(ConsoleIO $io, $opts = ['y|y' => FALSE]) {
    $env = Robo::config()->get('local_environment');
    $projectRoot = $this->getLocalEnvRoot();
    $testsRoot = $projectRoot . '/tests/playwright';

    $shouldProceed = TRUE;
    if (!$opts['y']) {
      $shouldProceed = $io->confirm("This action will generate/update Playwright VRT scaffolding in $testsRoot. Continue?", TRUE);
    }
    if (!$shouldProceed) {
      $io->warning('Playwright VRT init skipped.');
      return 0;
    }

    // Install and enable the required Drupal modules.
    $this->taskExec($env . " composer require 'drupal/automated_testing_kit' 'drupal/qa_accounts:^1.1'")->dir($projectRoot)->run();
    $this->taskExec($env . ' drush en automated_testing_kit qa_accounts -y')->dir($projectRoot)->run();

    // Collect env config after installs succeed so prompts aren't lost on failure.
    $vrtEnv = $this->collectVrtEnvConfig($io, $projectRoot);
    $defaultBaseUrl = $this->getDefaultBaseUrl($projectRoot);

    // Scaffold the Playwright workspace.
    $assets = dirname(__DIR__, 4) . '/assets/templates/playwright/';
    $this->taskFilesystemStack()
      ->mkdir($testsRoot)
      ->mkdir($testsRoot . '/tests/vrt')
      ->mkdir($testsRoot . '/tests/support')
      ->mkdir($testsRoot . '/tests/data')
      ->mkdir($testsRoot . '/css')
      ->copy($assets . 'package.json', $testsRoot . '/package.json', TRUE)
      ->copy($assets . 'playwright.config.js', $testsRoot . '/playwright.config.js', TRUE)
      ->copy($assets . 'playwright.atk.config.js', $testsRoot . '/playwright.atk.config.js', TRUE)
      ->copy($assets . 'css/screenshotGlobalStyle.css', $testsRoot . '/css/screenshotGlobalStyle.css', TRUE)
      ->copy($assets . 'tests/support/4k_utilities.js', $testsRoot . '/tests/support/4k_utilities.js', TRUE)
      ->copy($assets . 'tests/vrt/common.spec.js', $testsRoot . '/tests/vrt/common.spec.js', TRUE)
      ->copy($assets . 'data/vrtCommonPages.json', $testsRoot . '/tests/data/vrtCommonPages.json', TRUE)
      ->copy($assets . '.nvmrc', $testsRoot . '/.nvmrc', TRUE)
      ->run();

    if (!file_exists($testsRoot . '/README.md')) {
      $this->taskFilesystemStack()->copy($assets . 'README.md', $testsRoot . '/README.md')->run();
    }

    // Write .gitignore entries idempotently.
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
      ->appendUnlessMatches('/\*\.spec\.js-snapshots/', "*.spec.js-snapshots\n")
      ->run();

    // Replace placeholders in copied config files.
    $drushCmd = $this->getDefaultDrushCommand();
    $pantheonSite = Robo::config()->get('remote_sitename') ?: 'remote-pantheon-site-machine-name';
    $this->taskReplaceInFile($testsRoot . '/playwright.config.js')
      ->from('__DEFAULT_BASE_URL__')
      ->to($defaultBaseUrl)
      ->run();
    $this->taskReplaceInFile($testsRoot . '/playwright.atk.config.js')
      ->from('__DRUSH_CMD__')
      ->to($drushCmd)
      ->run();
    $this->taskReplaceInFile($testsRoot . '/playwright.atk.config.js')
      ->from('__PANTHEON_SITE__')
      ->to($pantheonSite)
      ->run();

    // Write the .env file with collected values.
    $this->writeVrtEnvFile($testsRoot, $vrtEnv);

    // Install dependencies and Playwright browsers.
    $nvmDir = getenv('NVM_DIR');
    if ($nvmDir && file_exists($testsRoot . '/.nvmrc')) {
      $nvmScript = rtrim($nvmDir, '/') . '/nvm.sh';
      if (!file_exists($nvmScript)) {
        throw new AbortTasksException("NVM script not found at $nvmScript.");
      }
      $command = 'export NVM_DIR=' . escapeshellarg($nvmDir)
        . ' && . ' . escapeshellarg($nvmScript)
        . ' && cd ' . escapeshellarg($testsRoot)
        . ' && nvm install'
        . ' && npm install --no-audit --no-fund'
        . ' && npx playwright install --with-deps';
      $this->taskExec($command)->run();
    }
    else {
      $this->taskExec($env . ' npm install')->dir($testsRoot)->run();
      $this->taskExec($env . ' npx playwright install --with-deps')->dir($testsRoot)->run();
    }
  }

  /**
   * Collect values for the Playwright VRT .env file.
   */
  private function collectVrtEnvConfig(ConsoleIO $io, string $projectRoot) {
    $baselineTerminusEnv = Robo::config()->get('remote_canonical_env') ?: 'live';
    $baselineTerminusSite = Robo::config()->get('remote_sitename') ?: '';
    $baselineUrl = $this->getDefaultBaselineUrl($baselineTerminusEnv, $baselineTerminusSite);
    $candidateUrl = $this->getDefaultBaseUrl($projectRoot);

    $baselineUrl = $io->ask('Please enter the baseline Url', $baselineUrl);
    $baselineTerminusEnv = $io->ask('Please enter baseline Terminus env', $baselineTerminusEnv);
    $baselineTerminusSite = $io->ask('Please enter baseline Terminus Site', $baselineTerminusSite);
    $candidateUrl = $io->ask('Please enter The candidate url(local)', $candidateUrl);

    return [
      'baseline_url' => trim((string) $baselineUrl),
      'candidate_url' => trim((string) $candidateUrl),
      'baseline_terminus_env' => trim((string) $baselineTerminusEnv),
      'baseline_terminus_site' => trim((string) $baselineTerminusSite),
    ];
  }

  /**
   * Write Playwright VRT environment variables to tests/playwright/.env.
   */
  private function writeVrtEnvFile(string $testsRoot, array $vrtEnv) {
    $contents = implode("\n", [
      'BASELINE_URL="' . $this->escapeDotEnvDoubleQuotedValue($vrtEnv['baseline_url']) . '"',
      'CANDIDATE_URL="' . $this->escapeDotEnvDoubleQuotedValue($vrtEnv['candidate_url']) . '"',
      'BASELINE_TERMINUS_ENV=' . $vrtEnv['baseline_terminus_env'],
      'BASELINE_TERMINUS_SITE=' . $vrtEnv['baseline_terminus_site'],
    ]) . "\n";

    $this->taskWriteToFile($testsRoot . '/.env')
      ->text($contents)
      ->run();
  }

  /**
   * Escape a value for use in a double-quoted dotenv assignment.
   */
  private function escapeDotEnvDoubleQuotedValue(string $value) {
    return str_replace(
      ["\\", '"', "\n", "\r"],
      ["\\\\", '\\"', '\\n', ''],
      $value
    );
  }

  /**
   * Infer a baseline URL from remote Terminus config when available.
   */
  private function getDefaultBaselineUrl(string $baselineTerminusEnv, string $baselineTerminusSite) {
    if (Robo::config()->get('remote_platform') === 'pantheon' && $baselineTerminusEnv && $baselineTerminusSite) {
      return 'https://' . $baselineTerminusEnv . '-' . $baselineTerminusSite . '.pantheonsite.io';
    }

    return '';
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

