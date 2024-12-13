<?php

namespace Fire\Robo\Plugin\Commands;

use Robo\Symfony\ConsoleIO;
use Robo\Robo;

/**
 * Provides a wrapper for Terminus, Acquia CLI, and Platform.sh CLI to get login URLs.
 */
class PlatformUliCommand extends FireCommandBase {

  /**
   * Get the login URL for a site and environment.
   *
   * Usage Example: fire platform:uli pr-123
   *
   * @command platform:uli
   * @aliases uli
   * @usage fire platform:uli <env>
   *
   * @param $args The environment.
   */
  public function uli(ConsoleIO $io, array $args) {
    // Ensure the correct number of arguments are passed
    if (count($args) < 1) {
      $io->note('Usage: fire platform:uli <env>');
      return;
    }

    // Assign environment variable directly from arguments
    $env = $args[0]; // First argument: environment (e.g., pr-123)

    // Get platform and site name information (Pantheon, Acquia, Platform.sh)
    $platform = Robo::config()->get('remote_platform'); // Set in Robo config for your project
    $remoteSiteName = Robo::config()->get('remote_sitename'); // Get the remote site name from the config

    // Check CLI tool status
    $cliStatus = $this->checkCliTools($platform);

    if (!$cliStatus) {
      $io->note('Required CLI tools are not installed or configured properly.');
      return;
    }

    // Determine the command based on the platform
    switch ($platform) {
      case 'pantheon':
        $command = "terminus drush $remoteSiteName.$env -- uli";
        break;

      case 'acquia':
        $command = "acli drush $remoteSiteName.$env -- uli";
        break;

      case 'platformsh':
        $command = "platform ssh --site=$remoteSiteName --env=$env drush uli";
        break;

      default:
        $io->note('Unknown platform or missing configuration for platform detection.');
        return;
    }

    // Execute the command
    $tasks = $this->collectionBuilder($io);
    $tasks->addTask($this->taskExec($command)->printOutput(TRUE));

    return $tasks;
  }

  /**
   * Checks if the necessary CLI tools are installed for the given platform.
   *
   * @param string $platform The platform (pantheon, acquia, platformsh).
   *
   * @return bool True if the CLI tools are found, false otherwise.
   */
  private function checkCliTools(string $platform) {
    switch ($platform) {
      case 'pantheon':
        return $this->getCliToolStatus('terminus');
      case 'acquia':
        return $this->getCliToolStatus('acli');
      case 'platformsh':
        return $this->getCliToolStatus('platform');
      default:
        return false;
    }
  }
}
