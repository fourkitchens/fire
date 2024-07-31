<?php

namespace Fire;

use Robo\Common\ConfigAwareTrait;
use \Robo\Config\Config;
use Robo\Robo;
use Robo\Runner;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Consolidation\AnnotatedCommand\CommandFileDiscovery;

class FireApp {

  const APPLICATION_NAME = 'fire';
  const REPOSITORY = 'fourkitchens/fire';

  use ConfigAwareTrait;

  /**
   * The Robo Runner.
   *
   * @var \Robo\Runner
   */
  private $runner;

  /**
   * The currently available commands.
   *
   * @var array
   */
  private $commandClasses;

  /**
   * The Symfony Console app
   *
   * @var \Symfony\Component\Console\Application
   */
  private $application;

  /**
   * Construct function for the fire app.
   *
   * @param \Robo\Config\Config $config
   *   The Fires config.
   * @param mixed $classLoader
   *   The autoload class.
   * @param \Symfony\Component\Console\Input\InputInterface $input
   *   The user Input.
   * @param \Symfony\Component\Console\Output\OutputInterface $output
   *   The Command out.
   */
  public function __construct(Config $config, $classLoader, InputInterface $input = NULL, OutputInterface $output = NULL) {
    // Automatically setting the local env config (lando or ddev) or getting it from the config file.
    if (!$config->get('local_environment') && $localEnv = $this->getLocalEnv()) {
      $config->set('local_environment', $localEnv);
    }

    // Create applicaton.
    $this->setConfig($config);
    $appVersion = trim(file_get_contents(dirname(__DIR__, 1) . '/VERSION'));
    $this->application = new Application(self::APPLICATION_NAME, $appVersion);

    // Create and configure container.
    $container = Robo::createContainer($this->application, $config);
    Robo::finalizeContainer($container);

    // Looking for existing commands.
    $discovery = new CommandFileDiscovery();
    $discovery->setSearchPattern('*Command.php');

    // Discover the class commands.
    $filesystem = new Filesystem();
    $projectCommandClasses = [];
    $customDir = str_replace('vendor/fourkitchens/', '', __DIR__) . '/Commands/';
    if ($filesystem->exists($customDir)) {
      $projectCommandClasses = $discovery->discover($customDir, '\FourKitchens\FireCustom\Commands');
    }
    $mainCommandClasses = $discovery->discover(__DIR__ . '/Robo/Plugin/Commands/', '\Fire\Robo\Plugin\Commands');
    $this->commandClasses = array_merge($mainCommandClasses, $projectCommandClasses);

    // Instantiate Robo Runner.
    $this->runner = new Runner();
    $this->runner->setContainer($container);
    $this->runner->setSelfUpdateRepository(self::REPOSITORY);
    $this->runner->setClassLoader($classLoader);
  }

  /**
   * Excutes the requested command.
   *
   * @param \Symfony\Component\Console\Input\InputInterface $input
   * @param \Symfony\Component\Console\Output\OutputInterface $output
   */
  public function run(InputInterface $input, OutputInterface $output) {
    $command = $input->getFirstArgument();

    // If the command is a known FIRE command, run it, otherwise pass to Lando/DDEV.
    $status_code = $this->isFireCommand($command) 
    ? $this->runner->run($input, $output, $this->application, $this->commandClasses) 
    : $this->passToLocalEnv($command, $input, $output);

    return $status_code;
  }

  /**
   * Checks if the command is known to FIRE.
   *
   * @param string $command
   *   The command to check.
   *
   * @return bool
   *   TRUE if the command is known to FIRE, FALSE otherwise.
   */
  private function isFireCommand($command) {
    foreach ($this->commandClasses as $commandClass) {
      if (strpos($commandClass, $command) !== false) {
        return true;
      }
    }
    return false;
  }

  /**
   * Passes the command to the local environment (Lando/DDEV).
   *
   * @param string $command
   *   The command to pass.
   * @param \Symfony\Component\Console\Input\InputInterface $input
   * @param \Symfony\Component\Console\Output\OutputInterface $output
   *
   * @return int
   *   The exit code of the executed command.
   */
  private function passToLocalEnv($command, InputInterface $input, OutputInterface $output) {
    $env = $this->getLocalEnv();
    $args = array_slice($_SERVER['argv'], 2); // Skip 'fire' and the command itself.
    $taskExec = null;

    switch ($env) {
      case 'lando':
        $taskExec = $this->taskExec('lando ' . $command)->args($args);
        break;
      case 'ddev':
        $taskExec = $this->taskExec('ddev ' . $command)->args($args);
        break;
      default:
        $output->writeln("Unknown local environment: $env");
        return 1;
    }

    return $taskExec->run()->getExitCode();
  }

  /**
   * Returns the local env (ddev, lando).
   */
  private function getLocalEnv() {
    $projectRoot = dirname(__DIR__, 4);
    if (file_exists($projectRoot . '/.lando.yml')) {
      return 'lando';
    }
    elseif (file_exists($projectRoot . '/.ddev/config.yaml')) {
      return 'ddev';
    }
    else {
      return FALSE;
    }
  }

}
