<?php

namespace Fire\Robo\Plugin\Commands;

use Fire\Robo\Plugin\Commands\FireCommandBase;
use Consolidation\AnnotatedCommand\CommandFileDiscovery;
use Robo\Robo;
use Robo\Collection\CollectionBuilder;
use Robo\Symfony\ConsoleIO;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Provides a command to overwrite others command.
 */
class OverwriteCommand extends FireCommandBase {

  /**
   * overwrite a command.
   *
   * Usage Example: fire overwrite
   *
   * @command local:overwrite
   * @aliases ow, oc, overwrite, overwrite-command
   * @usage fire overwrite
   */
  public function overwrite(ConsoleIO $io) {
    $env = Robo::config()->get('local_environment');
    $tasks = $this->collectionBuilder($io);
    $requiredPath = 'fire/src/Robo/Plugin/Commands/';

    // Step 1.
    if (!$this->composerAutoload($tasks, $env, $requiredPath)) {
      return;
    }

    // Step 2.
    $this->createCustomPath($requiredPath);

    // Step 3.
    $newCommand = $this->createCustomCommand($tasks, $requiredPath);
    if (is_null($newCommand)) {
      $this->say("There was an error and the command could not be overwritten.");
      return;
    }

    return $tasks;
  }

  /**
   * Add Autoload to the composer file.
   *
   * @param CollectionBuilder $tasks
   * @param string $env
   * @param string $requiredPath
   * @return void
   */
  private function composerAutoload(CollectionBuilder &$tasks, string $env, string $requiredPath) {
    $filePath = 'composer.json';
    $requiredNamespace = 'fourkitchens\\fire\\';

    if (!file_exists($filePath)) {
      $this->say('The "composer.json" file does not exist.');
      return FALSE;
    }

    $needsUpdate = FALSE;
    $composerJson = json_decode(file_get_contents($filePath), true);
    if (isset($composerJson['autoload']['psr-4'])) {
      if (isset($composerJson['autoload']['psr-4'][$requiredNamespace])) {
        $this->say('The namespace already exists in the "composer.json" file.');
      } else {
        $needsUpdate = TRUE;
        $composerJson['autoload']['psr-4'][$requiredNamespace] = $requiredPath;
        $this->say('The namespace has been added to the "composer.json" file.');
      }
    } else {
      $needsUpdate = TRUE;
      $composerJson['autoload']['psr-4'] = [
        $requiredNamespace => $requiredPath,
      ];
      $this->say('The namespace has been added to the "composer.json" file.');
    }

    if ($needsUpdate) {
      file_put_contents($filePath, json_encode($composerJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
      $tasks->addTask($this->taskExec("$env composer dump-autoload"));
    }
  
    return TRUE;
  }

  /**
   * Create custom path.
   *
   * @param string $requiredPath
   * @return void
   */
  private function createCustomPath(string $requiredPath) {
    $filesystem = new Filesystem();

    if (!$filesystem->exists($requiredPath)) {
      $filesystem->mkdir($requiredPath, 0755);
      $this->say('The path has been created.');
    } else {
      $this->say('The path already exists.');
    }
  }

  /**
   * Create the custom command.
   *
   * @param CollectionBuilder $tasks
   * @param string $requiredPath
   * @return void
   */
  private function createCustomCommand(CollectionBuilder &$tasks, string $requiredPath) {
    $root = $this->getLocalEnvRoot();
    $currentPath = __DIR__;
    $discovery = new CommandFileDiscovery();
    $discovery->setSearchPattern('*Command.php');
    $commandClasses = $discovery->discover($currentPath);

    $commands = [];
    foreach ($commandClasses as $cmdName) {
      $key = str_replace('Command', '', $cmdName);

      if ($cmdName === 'OverwriteCommand') {
        continue;
      }

      $commands[$key] = $cmdName;
    }

    // Get input and output.
    $input = $this->input();
    $output = $this->output();

    $output->writeln('');
    // Ask the user what command they want to overwrite.
    $helper = new QuestionHelper();
    $question = new ChoiceQuestion(
      'Select a command to overwrite:' . PHP_EOL,
      array_keys($commands),
    );

    $question->setErrorMessage('Invalid %s command.');
    $selectedCommand = $helper->ask($input, $output, $question);

    $filesystem = new Filesystem();
    $cmdFile = $commands[$selectedCommand] . '.php';
    $origin = "{$currentPath}/{$cmdFile}";
    $dest = "{$requiredPath}{$cmdFile}";
    if (!$filesystem->exists($origin)) {
      $this->say("Could not locate file '$origin'.");
      return NULL;
    }
    elseif ($filesystem->exists($dest))  {
      $this->say("The '$selectedCommand' command has already been overwritten previously.");
    }
    else {
      $this->say("The '$selectedCommand' command was successfully overwritten.");
      $tasks->addTask($this->taskExec("cp -a $origin {$root}/{$dest}"));
    }

    $this->say("Now you can edit it with the following command: ' $ code $dest '.");

    return $dest;
  }

}
