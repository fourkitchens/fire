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

  private $runner;
  private $commandClasses;
  private $application;
  public function __construct(Config $config, $classLoader, InputInterface $input = NULL, OutputInterface $output = NULL) {

    //cho(serialize($config));
    $config->set('local_environment', 'test');
    // Create applicaton.

    $this->setConfig($config);
    $appVersion = trim(file_get_contents(dirname(__DIR__, 1) . '/VERSION'));
    $this->application = new Application(self::APPLICATION_NAME, $appVersion);

    // Create and configure container.
    $container = Robo::createContainer($this->application, $config);
    Robo::finalizeContainer($container);
    $discovery = new CommandFileDiscovery();
    $discovery->setSearchPattern('*Command.php');
    $this->commandClasses = $discovery->discover(__DIR__ . '/Robo/Plugin/Commands/', '\Fire\Robo\Plugin\Commands');

    // Instantiate Robo Runner.
    $this->runner = new Runner();
     $this->runner->setContainer($container);
     $this->runner->setSelfUpdateRepository(self::REPOSITORY);
     $this->runner->setClassLoader($classLoader);
  }

  public function run(InputInterface $input, OutputInterface $output) {
    $status_code = $this->runner->run($input, $output, $this->application, $this->commandClasses);
    return $status_code;
  }

}
