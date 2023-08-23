<?php
use Fire\FireApp;
use Robo\Robo;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;

// Loading autoloder class.
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
  $autoloaderPath = __DIR__ . '/vendor/autoload.php';
} elseif (file_exists(__DIR__ . '/../../autoload.php')) {
  $autoloaderPath = __DIR__ . '/../../autoload.php';
}
elseif (file_exists(__DIR__ . '/../../../autoload.php')) {
  $autoloaderPath = __DIR__ . '/../../../autoload.php';
}
else {
  die("Could not find autoloader. Run 'composer install'.");
}
$classLoader = require $autoloaderPath;
$projectRoot = dirname(__DIR__, 4);
$configFile = $projectRoot . '/fire.yml';
$additionalConfigFile = $projectRoot . '/fire.local.yml';
// If there is not config file, the user should create one.
if (file_exists($configFile)) {
  $config = [$configFile];
  if (file_exists($additionalConfigFile)) {
    // Loading local config overrides.
    $config[] = $additionalConfigFile;
  }
  $input = new ArgvInput($argv);
  $output = new ConsoleOutput();
  $config = Robo::createConfiguration($config);
  $app = new FireApp($config, $classLoader, $input, $output);
  $status_code = $app->run($input, $output);
  exit($status_code);
}
else {
  die("Could not find the fire.yml file in your project root, please create it and try again.");
}
