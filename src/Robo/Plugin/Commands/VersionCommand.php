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

class helloCommand extends \Robo\Tasks {

  /**
   * Demostrate varible args.
   *
   * @command hello:world
   * @param array $args Arguments to print
   * @aliases hello
   * @usage test
   *   Prints hello
   */
  public function helloWorld (array $args) {
    return 'Hello' . implode(' ', $args);
  }
}
