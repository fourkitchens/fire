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

class VersionCommand extends \Robo\Tasks {

  /**
   * Demostrate varible args.
   */
  public function helloWorld (ConsoleIO $io) {
    $io->say('HELLO');
  }
}
