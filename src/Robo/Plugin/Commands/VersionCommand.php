<?php

namespace Fire\Robo\Plugin\Commands;

use Robo\Result;

use Consolidation\AnnotatedCommand\CommandData;
use Consolidation\OutputFormatters\Options\FormatterOptions;
use Consolidation\OutputFormatters\StructuredData\RowsOfFields;
use Consolidation\OutputFormatters\StructuredData\PropertyList;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Robo\Symfony\ConsoleIO;
use Robo\Robo;

class VersionCommand extends \Robo\Tasks {

  /**
   * Shows the currently installed fire version.
   */
  public function version (ConsoleIO $io) {
    $env = Robo::config()->get('enviroment');
    echo($env);
    $appVersion = trim(file_get_contents(__DIR__ . '/VERSION'));
    $io->say('Fire version: ' . $appVersion . ' ' . __DIR__ . '/VERSION');
  }
}
