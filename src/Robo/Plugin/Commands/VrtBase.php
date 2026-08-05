<?php

namespace Fire\Robo\Plugin\Commands;

use Ramsey\Collection\Exception\OutOfBoundsException;
use Robo\Common\TaskIO;
use Robo\Exception\AbortTasksException;
use Robo\Exception\TaskException;
use Robo\Exception\TaskExitException;
use Robo\Symfony\ConsoleIO;
use Robo\Robo;

/**
 * Provides shared methods used by many vrt commands.
 */
class VrtBase extends FireCommandBase {

  /**
   * The IO object.
   *
   * @var ConsoleIO
   */
  protected $io;

  /**
   * Resolve which VRT tool should handle the command.
   */
  protected function resolveVrtTool(array $opts, ConsoleIO $io) {
    $tool = strtolower((string) ($opts['tool'] ?? 'auto'));
    if (in_array($tool, ['backstopjs', 'backstopjs(deprecated)'], TRUE)) {
      $tool = 'backstop';
    }

    $root = $this->getLocalEnvRoot();
    $hasBackstop = file_exists($root . '/tests/backstop/backstop.json') || file_exists($root . '/tests/backstop/backstop-local.json');
    $hasPlaywright = file_exists($root . '/tests/playwright/package.json') || file_exists($root . '/tests/playwright/playwright.config.js');

    if (in_array($tool, ['backstop', 'playwright'], TRUE)) {
      if ($tool === 'backstop' && !$hasBackstop) {
        throw new AbortTasksException('Backstop VRT config was not found. Run fire vrt:init and choose Backstop first.');
      }
      if ($tool === 'playwright' && !$hasPlaywright) {
        throw new AbortTasksException('Playwright VRT config was not found. Run fire vrt:playwright:init first.');
      }
      return $tool;
    }

    if ($tool !== 'auto') {
      throw new AbortTasksException("Invalid VRT tool '$tool'. Use backstop, playwright, or auto.");
    }

    if ($hasBackstop && $hasPlaywright) {
      if (Robo::config()->isInteractive()) {
        return $io->choice('Both Backstop and Playwright VRT are configured. Which tool do you want to run?', ['playwright', 'backstop'], 0);
      }
      return 'playwright';
    }
    if ($hasPlaywright) {
      return 'playwright';
    }
    if ($hasBackstop) {
      return 'backstop';
    }

    throw new AbortTasksException('No VRT configuration was found. Run fire vrt:init first.');
  }

  /**
   * Similar to taskExec(), but for Backstop commands.
   *
   * @param ConsoleIO $io
   *
   * @param string $backstopCommand
   *  E.g. 'reference', or 'test'
   *
   * @return \Robo\Collection\CollectionBuilder|\Robo\Task\Base\Exec
   */
  protected function backstopTaskExec(ConsoleIO $io, string $backstopCommand) {
    $this->io = $io;
    try {
      $this->io->info("Running Backstop command: $backstopCommand...");

      $env = Robo::config()->get('local_environment');

      if ($backstopCommand !== 'init') {
        $hostingEnvironment = $this->getHostingEnvironment($backstopCommand);
        $this->refreshCookies($hostingEnvironment);
      }
      if ($env === 'lando') {
        $config = '/app/tests/backstop/backstop-local.json';
        $command = 'cd /app/tests/backstop';
        $command .= ' && backstop ' . $backstopCommand;
        $command .= ' --config=' . $config;
        return $this->taskExec( $env . ' ssh -s backstopserver -c "' . $command . '"');
      }
      elseif ($env === 'ddev') {
        $config = '/src/backstop-local.json';
        $command = 'backstop ' . $backstopCommand;
        $command .= ' --config=' . $config;
        return $this->taskExec($env . ' ' . $command);
      }
      throw new AbortTasksException('Unknown local environment.');
    }
    catch (\Exception $exception) {
      // @todo For some reason if you only throw an exception nothing is printed
      // to the CLI.
      $this->io->error($exception->getMessage());
      throw $exception;
    }
  }

  /**
   * Regenerate cookies for this hosting environment, if there's a script for it.
   *
   * @param string $environment
   *
   * @return void
   */
  protected function refreshCookies(string $environment) {
    $shellScript = $this->getLocalEnvRoot()  . '/tests/backstop/get-logged-in-cookie.sh';
    if (file_exists($shellScript)) {
      $this->io->info("Running cookie generation file at $shellScript.");
      $this->taskExec($shellScript . ' ' . $environment)->run();
    }
    else {
      $this->io->info("No cookie generation file found at $shellScript.  You will only be able to run scenarios as an anonymous visitor.");
    }
  }

  /**
   * What hosting environment are we working with for the given Backstop command.
   *
   * @param $backstopCommand
   *
   * @return string
   *   E.g. 'live', or 'pr-24'
   *
   * @throws \Robo\Exception\AbortTasksException
   */
  protected function getHostingEnvironment($backstopCommand) {
    $configFile = $this->getLocalEnvRoot() . '/tests/backstop/backstop-local.json';
    $configContents = file_get_contents($configFile);
    $config = json_decode($configContents);
    if (empty($config)) {
      throw new AbortTasksException("The config file $configFile could not be parsed.  Maybe there's a JSON syntax error?");
    }
    if ($backstopCommand === 'reference') {
      $key = 'referenceEnvironment';
    }
    else {
      $key = 'testEnvironment';
    }
    if (isset($config->{$key}) && is_string($config->{$key}) && $config->{$key}) {
      return $config->{$key};
    }
    throw new AbortTasksException("$key was not found in $configFile.  These will be filled automatically by FIRE as long as they exist in backstop.json as just \"$key\": \"\",");
  }

}
