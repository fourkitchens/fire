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
   * Similar to taskExec(), but for Playwright commands.
   *
   * @param ConsoleIO $io
   * @param string $playwrightCommand
   *   E.g. 'test --grep @vrt'
   *
   * @return \Robo\Collection\CollectionBuilder|\Robo\Task\Base\Exec
   */
  protected function playwrightTaskExec(ConsoleIO $io, string $playwrightCommand) {
    $this->io = $io;
    $playwrightRoot = $this->getLocalEnvRoot() . '/tests/playwright';
    if (!is_dir($playwrightRoot)) {
      throw new AbortTasksException("Playwright tests folder not found at $playwrightRoot. Run 'fire vrt:playwright:init' first.");
    }
    return $this->taskExec('npx playwright ' . $playwrightCommand)->dir($playwrightRoot);
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
