<?php

namespace Fire;

use Robo\Common\ConfigAwareTrait;
use \Robo\Config\Config;
use Robo\Robo;
use Robo\Runner;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
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

    // Automatically setting the local env config (lando or acquia) or getting it from the config file.
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
    $this->commandClasses = $discovery->discover(__DIR__ . '/Robo/Plugin/Commands/', '\Fire\Robo\Plugin\Commands');

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
    $status_code = $this->runner->run($input, $output, $this->application, $this->commandClasses);
    return $status_code;
  }

  /**
   * Returns the local env (acquia, lando).
   */
  private function getLocalEnv() {
    $projectRoot = dirname(__DIR__, 4);
    if (file_exists($projectRoot . '/.lando.yml')) {
      return 'lando';
    }
    elseif (file_exists($projectRoot . '/.ddev/config.yaml')) {
      return 'acquia';
    }
    else {
      return FALSE;
    }
  }

}
