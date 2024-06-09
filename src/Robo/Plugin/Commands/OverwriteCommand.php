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
    $this->createCustomDirectory($commandPath);

    // Step 3: Copy the current command to the new path.
    $newCommandFile = $this->createCustomCommand($commandPath);
    if (is_null($newCommandFile)) {
      $this->say("There was an error and the command could not be overwritten.");
      return;
    }

    // Step 4: Ask for type of overwrite.
    $selectedType = $this->askOverwriteType();
    $this->say('');

    // Step 5: Overwrite the namaspace and the class name.
    $this->updateCustomCommand($namespace, $newCommandFile, $selectedType);

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
  private function createCustomDirectory(string $commandPath) {
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
    asort($commands);

    // Get input and output.
    $input = $this->input();
    $output = $this->output();

    $output->writeln('');
    // Ask to the user what command they want to overwrite.
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
   * Create a function to ask for the type of overwrite.
   */
  private function askOverwriteType() {
    $typeOptions = ['Full', 'Partial'];
    // Get input and output.
    $input = $this->input();
    $output = $this->output();

    $output->writeln('');
    // Ask to the user what type of overwrite they want to use.
    $helper = new QuestionHelper();
    $question = new ChoiceQuestion(
      'Select the type of overwrite you want to use:' . PHP_EOL,
      $typeOptions,
    );

    $question->setErrorMessage('Invalid %s type.');
    $selectedType = $helper->ask($input, $output, $question);

    return $selectedType;
  }

  /**
   * This function update namespace and the class name
   *
   * @param string $namespace
   * @param string $filePath
   * @return void
   */
  private function updateCustomCommand(string $namespace, string $filePath, $type = 'partial') {
    if (!file_exists($filePath)) {
      $this->say("The '$filePath' doesn't exist.");
      return;
    }

    // Init a new file content.
    $newFileContent = '';

    // Read the file.
    $fileContent = file_get_contents($filePath);

    // Search values.
    preg_match('/public\s+function\s+(\w+)\s*\(/', $fileContent, $functionNameMatches);
    $firstFunctionName = isset($functionNameMatches[1]) ? $functionNameMatches[1] : '';
    preg_match_all('/^use\s+([^;]+);/m', $fileContent, $importedLibsMatches);
    preg_match('/namespace\s+(.+);/', $fileContent, $namespaceMatches);
    preg_match('/class\s+(\w+)/', $fileContent, $classNameMatches);
    preg_match('/(\/\*\*.*?\*\/\s+)?class\s+\w+(?:\s+extends\s+\w+)?\s*\{/s', $fileContent, $classMatches);
    preg_match('/(\/\*\*[\s\S]*?\*\/\s+)?public\s+function\s+' . $firstFunctionName . '\s*\([^)]*\)\s*\{[\s\S]*?\}/', $fileContent, $functionContentMatches);

    // Get the values from ReExp.
    $oldNamespace = isset($namespaceMatches[1]) ? $namespaceMatches[1] : '';
    $oldClassName = isset($classNameMatches[1]) ? $classNameMatches[1] : '';
    $fullClass = isset($classMatches[0]) ? $classMatches[0] : '';
    $firstFunctionContent = isset($functionContentMatches[0]) ? $functionContentMatches[0] : '';
    $firstFunctionContent = str_replace($fullClass, '', $firstFunctionContent);

    // Update the class name.
    $fullClass = preg_replace('/class\s+(\w+)/', "class Custom{$oldClassName}", $fullClass);
    // Update the inherited class.
    $fullClass = preg_replace('/extends\s+\w+/', "extends {$oldClassName}", $fullClass);
    // Update the function content.
    $newFirstFunctionContent = $this->generateFunctionContent($firstFunctionContent, $firstFunctionName, $type);
    $newFirstFunctionContent = preg_replace('/\{.*\}/s', '{' . $newFirstFunctionContent . "  }\n", $firstFunctionContent);

    // Generate the new file.
    $newFileContent .= "<?php\n\n";
    $newFileContent .= "namespace {$namespace}Commands;\n\n";
    $newFileContent .= "use {$oldNamespace}\\{$oldClassName};\n";
    $needsRoboLib = TRUE;
    if (!empty($importedLibsMatches[0])) {
      foreach ($importedLibsMatches[0] as $lib) {
        $newFileContent .= "{$lib}\n";
        $pos = strpos($lib, 'Robo\Robo');
        if ($pos !== false) {
          $needsRoboLib = FALSE;
        }
      }
    }
    if ($needsRoboLib) {
      $newFileContent .= "use Robo\Robo;\n";
    }
    $newFileContent .= "\n{$fullClass}";
    $newFileContent .= $newFirstFunctionContent;
    $newFileContent .= "\n}\n";
    // End generate the new file.

    // Save the file.
    $filesystem = new Filesystem();
    try {
      $filesystem->dumpFile($filePath, $newFileContent);
    } catch (IOException $e) {
      $this->say("{$e->getMessage()}");
    }
  }

  /**
   * This function returns all the parameters of the first function.
   *
   * @param string $fileContent
   * @return void
   */
  private function getFunctionParameters(string $fileContent) {
    // Get function parameters.
    $firstFunctionParameters = '';
    if (preg_match('/public\s+function\s+\w+\s*\(([^)]*)\)/', $fileContent, $matches)) {
      $parametersString = $matches[1];
      $parametersArray = array_map('trim', explode(',', $parametersString));

      $aux = [];
      foreach ($parametersArray as $param) {
        $current = explode(' ', $param);
        if (!empty($current)) {
          if (count($current) > 1) {
            $aux[] = $current[1];
          }
          else {
            $aux[] = $current[0];
          }
        }
      }

      if (!empty($aux)) {
        $firstFunctionParameters = implode(', ', $aux);
      }
    }

    return $firstFunctionParameters;
  }

  /**
   * This function generates the function content.
   *
   * @param string $fileContent
   * @param string $firstFunctionName
   * @param string $type
   * @return void
   */
  private function generateFunctionContent(string $fileContent, string $firstFunctionName, string $type) {
    $newFunctionContent = "\n";

    switch ($type) {
      case 'Partial':
        $firstFunctionParameters = $this->getFunctionParameters($fileContent);
        $newFunctionContent .= "    \$tasks = parent::{$firstFunctionName}({$firstFunctionParameters});\n";
        break;

      case 'Full':
        default:
        $newFunctionContent .= "    \$tasks = \$this->collectionBuilder(\$io);\n";
        break;
    }

    $newFunctionContent .= "    \$env = Robo::config()->get('local_environment');\n\n";
    $newFunctionContent .= "    // Add here your new code.\n";
    $newFunctionContent .= "    \$tasks->addTask(\$this->taskExec(\$env . ' drush cr'));\n\n";
    $newFunctionContent .= "    return \$tasks;\n";

    return $newFunctionContent;
  }

}
