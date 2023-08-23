<?php
use Consolidation\AnnotatedCommand\CommandFileDiscovery;
use Symfony\Component\Console\Output\ConsoleOutput;
use Robo\Runner;
use League\Container\Container;
use Robo\Robo;

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


$input = new \Symfony\Component\Console\Input\ArgvInput($argv);
$output = new \Symfony\Component\Console\Output\ConsoleOutput();
$configFile = dirname(__DIR__, 4) . '/fire.yml';
$config = Robo::createConfiguration([$configFile]);
$app = new \Fire\FireApp($config, $classLoader, $input, $output);
$status_code = $app->run($input, $output);
exit($status_code);
