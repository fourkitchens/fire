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
use Symfony\Component\Filesystem\Exception\IOException;

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
    $namespace = 'FourKitchens\\FireCustom\\';
    $src = 'fire/src/';
    $commandPath = $src . 'Commands/';

    // Step 1: Autoload my new commands.
    if (!$this->composerAutoload($tasks, $env, $namespace, $src)) {
      return;
    }

    // Step 2: Create the directory for the new commands.
    $this->createCustomPath($commandPath);

    // Step 3: Copy the current command to the new path.
    $newCommandFile = $this->createCustomCommand($commandPath);
    if (is_null($newCommandFile)) {
      $this->say("There was an error and the command could not be overwritten.");
      return;
    }

    // Step 4: Overwrite the namaspace and the class name.
    $this->updateCustomCommand($namespace, $newCommandFile);

    return $tasks;
  }

  /**
   * Add Autoload to the composer file.
   *
   * @param CollectionBuilder $tasks
   * @param string $env
   * @param string $commandPath
   * @return void
   */
  private function composerAutoload(CollectionBuilder &$tasks, string $env, string $namespace, string $src) {
    $composerPath = 'composer.json';

    if (!file_exists($composerPath)) {
      $this->say('The "composer.json" file does not exist.');
      return FALSE;
    }

    $needsUpdate = FALSE;
    $composerJson = json_decode(file_get_contents($composerPath), true);
    if (isset($composerJson['autoload']['psr-4'])) {
      if (isset($composerJson['autoload']['psr-4'][$namespace])) {
        $this->say('The namespace already exists in the "composer.json" file.');
      } else {
        $needsUpdate = TRUE;
        $composerJson['autoload']['psr-4'][$namespace] = $src;
        $this->say('The namespace has been added to the "composer.json" file.');
      }
    } else {
      $needsUpdate = TRUE;
      $composerJson['autoload']['psr-4'] = [
        $namespace => $src,
      ];
      $this->say('The namespace has been added to the "composer.json" file.');
    }

    if ($needsUpdate) {
      file_put_contents($composerPath, json_encode($composerJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
      $tasks->addTask($this->taskExec("$env composer dump-autoload"));
    }
  
    return TRUE;
  }

  /**
   * Create custom path.
   *
   * @param string $commandPath
   * @return void
   */
  private function createCustomPath(string $commandPath) {
    $filesystem = new Filesystem();

    if (!$filesystem->exists($commandPath)) {
      $filesystem->mkdir($commandPath, 0755);
      $this->say('The path has been created.');
    } else {
      $this->say('The path already exists.');
    }
  }

  /**
   * Create the custom command.
   *
   * @param CollectionBuilder $tasks
   * @param string $commandPath
   * @return void
   */
  private function createCustomCommand(string $commandPath) {
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
    $dest = "{$commandPath}Custom{$cmdFile}";

    if (!$filesystem->exists($origin)) {
      $this->say("Could not locate file '$origin'.");
      return NULL;
    }
    elseif ($filesystem->exists($dest))  {
      $this->say("The '$selectedCommand' command has already been overwritten previously.");
    }
    else {
      $this->say("The '$selectedCommand' command was successfully overwritten.");
      $this->taskExec("cp -a $origin $dest")->run();
    }

    $this->say("");
    $this->say("Now you can edit it with the following command:");
    $this->say("");
    $this->say("  $ code $dest");
    $this->say("");

    return $dest;
  }

  /**
   * This function update namespace and the class name
   *
   * @param string $namespace
   * @param string $filePath
   * @return void
   */
  private function updateCustomCommand(string $namespace, string $filePath) {
    $newNamespace = "namespace {$namespace}Commands;";

    if (!file_exists($filePath)) {
      $this->say("The '$filePath' doesn't exist.");
      return;
    }

    // Read the file.
    $fileContent = file_get_contents($filePath);

    // Update the namaspace.
    $fileContent = preg_replace('/namespace\s+.*;/', $newNamespace, $fileContent);

    // Update the class name.
    $fileContent = preg_replace_callback('/class\s+(\w+)/', function ($matches) {
      return 'class Custom' . $matches[1];
    }, $fileContent);

    // Save the file.
    $filesystem = new Filesystem();
    try {
      $filesystem->dumpFile($filePath, $fileContent);
    } catch (IOException $e) {
      $this->say("{$e->getMessage()}");
    }
  }

}
