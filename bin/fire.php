<?php

if (file_exists(__DIR__ . '/vendor/autoload.php')) {
  $autoloaderPath = __DIR__ . '/vendor/autoload.php';
} elseif (file_exists(__DIR__ . '/../../autoload.php')) {
  $autoloaderPath = __DIR__ . '/../../autoload.php';
} else {
  die("Could not find autoloader. Run 'composer install'.");
}

$classLoader = require $autoloaderPath;

// Customization variables
$appName = "fire";
$appVersion = trim(file_get_contents(__DIR__ . '/VERSION'));
$discovery = new \Consolidation\AnnotatedCommand\CommandFileDiscovery();
$discovery->setSearchPattern('*Command.php');
$commandClasses = $discovery->discover('/../src/Robi/Plugin/Commands/', '\Fire\Robo\Plugin\Commands');
$selfUpdateRepository = 'fourkitchens/fire';
$configurationFilename = 'fire.yml';

// Define our Runner, and pass it the command classes we provide.
$runner = new \Robo\Runner($commandClasses);
$runner
  ->setSelfUpdateRepository($selfUpdateRepository)
  ->setConfigurationFilename($configurationFilename)
  ->setClassLoader($classLoader);

// Execute the command and return the result.
$output = new \Symfony\Component\Console\Output\ConsoleOutput();
$statusCode = $runner->execute($argv, $appName, '0.1', $output);
exit($statusCode);
