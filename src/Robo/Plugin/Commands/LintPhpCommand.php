<?php

namespace Fire\Robo\Plugin\Commands;

use Robo\Symfony\ConsoleIO;

/**
 * Lint PHP command.
 */
class LintPhpCommand extends FireCommandBase {
  /**
   * Lint PHP
   *
   * Usage Example: fire local:lint:php
   *
   * @command local:lint:php
   * @aliases lint-php php-lint lintphp phplint
   *
   */
  public function drush(ConsoleIO $io, array $args) {
    $tasks = $this->collectionBuilder($io);
    $root = $this->getDrupalRoot();
    $tasks->addTask($this->taskExec("find $root/modules/custom $root/themes/custom ( -iname '*.php' -o -iname '*.inc' -o -iname '*.module' -o -iname '*.install' -o -iname '*.theme' ) '!' -path '*/node_modules/*' -print0 | xargs -0 -n1 -P8 php -l"));
    $tasks->addTask($this->taskExec("./vendor/bin/phpcs --standard=Drupal,DrupalPractice --extensions=php,module,inc,install,test,profile,theme,info,yml $root/modules/custom/ $root/themes/custom/"));

    return $tasks;
  }
}
