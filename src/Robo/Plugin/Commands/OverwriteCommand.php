<?php

namespace Fire\Robo\Plugin\Commands;

use Fire\Robo\Plugin\Commands\FireCommandBase;
use Consolidation\AnnotatedCommand\CommandFileDiscovery;
use Robo\Robo;
use Robo\Collection\CollectionBuilder;
use Robo\Symfony\ConsoleIO;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
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

    // Step 3: ask the user for the command they want to override.
    $selectedCommand = $this->askOverwriteCommand();

    // Step 4: Create or overwrithe a command.
    if ($selectedCommand === 'Custom') {
      // Create command from scratch.
      $this->createCustomCommand($namespace, $commandPath);
    }
    else {
      // Copy the current command to the new path.
      $this->overwriteExistingCommand($namespace, $commandPath, $selectedCommand);
    }

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
   */
  private function askOverwriteCommand() {
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
    $commands['Custom'] = 'Custom';

    $question = 'Select a command to overwrite:';
    $selectedCommand = $this->choiceQuestion($question, array_keys($commands));

    return $commands[$selectedCommand];
  }

  /**
   * This function override an existing command.
   *
   * @param string $namespace
   * @param string $commandPath
   * @param string $selectedCommand
   * @return void
   */
  private function overwriteExistingCommand(string $namespace, string $commandPath, string $selectedCommand) {
    $filesystem = new Filesystem();
    $currentPath = __DIR__;
    $cmdFile = "{$selectedCommand}.php";
    $origin = "{$currentPath}/{$cmdFile}";
    $dest = "{$commandPath}Custom{$cmdFile}";
    $writeFile = TRUE;

    if (!$filesystem->exists($origin)) {
      $this->say("Could not locate file '$origin'.");
      return NULL;
    }
    elseif ($filesystem->exists($dest))  {
      $this->say("The '$selectedCommand' command has already been overwritten previously.");
      $question = 'Would you like to replace it?';
      $options = ['No', 'Yes'];
      $response = $this->choiceQuestion($question, $options);

      if ($response === 'Yes') {
        $this->taskExec("rm $dest")->run();
      }
      else {
        $writeFile = FALSE;
      }
    }

    if ($writeFile) {
      $question = 'Select the type of overwrite you want to use:';
      $options = ['Full', 'Partial'];
      $selectedType = $this->choiceQuestion($question, $options);

      $this->say('');
      $this->taskExec("cp -a $origin $dest")->run();
      $this->say("The '$selectedCommand' command was successfully overwritten.");
      $this->updateCustomCommand($namespace, $dest, $selectedType);
    }

    $this->say('');
    $this->say('You can edit it with the following command:');
    $this->say('');
    $this->say("  $ code $dest");
    $this->say('');
  }

  /**
   * Undocumented function
   *
   * @return void
   */
  private function createCustomCommand($namespace, $commandPath) {
    $filesystem = new Filesystem();
    $currentPath = __DIR__;
    $origin = "{$currentPath}/tpl/__custom.txt";

    $this->say('Creating a new command.');
    $commandName = $this->generalQuestion('Enter the name of the new command:');
    $commandAlias = $this->generalQuestion('Enter the alias of the new command:');
    $commandDescription = $this->generalQuestion('Enter a description for the new command:');
    $commandName = $this->convertToCamelCase($commandName);
    $commandFunction = lcfirst($commandName);
    $commandName = "Custom{$commandName}Command";
    $commandFile = "{$commandPath}{$commandName}.php";

    $writeFile = TRUE;
    if ($filesystem->exists($commandFile))  {
      $this->say("The '$commandName' command has already exist.");
      $question = 'Would you like to replace it?';
      $options = ['No', 'Yes'];
      $response = $this->choiceQuestion($question, $options);

      if ($response === 'Yes') {
        $this->taskExec("rm $commandFile")->run();
      }
      else {
        $writeFile = FALSE;
      }
    }

    if ($writeFile) {
      $this->taskExec("cp -a $origin $commandFile")->run();

      // Read the file.
      $fileContent = file_get_contents($commandFile);
      // Update Data.
      $fileContent = str_replace(
        [
          '<namespace>',
          '<commandName>',
          '<commandDescription>',
          '<commandAlias>',
          '<commandFunction>',
          '<commandFire>',
        ],
        [
          $namespace . 'Commands',
          $commandName,
          $commandDescription,
          $commandAlias,
          $commandFunction,
          strtolower($commandFunction),
        ],
        $fileContent
      );

      try {
        $filesystem->dumpFile($commandFile, $fileContent);
      } catch (IOException $e) {
        $this->say("{$e->getMessage()}");
      }
    }

    $this->say('');
    $this->say('You can edit it with the following command:');
    $this->say('');
    $this->say("  $ code $commandFile");
    $this->say('');
  }

  /**
   * This function update namespace and the class name.
   *
   * @param string $namespace
   * @param string $filePath
   * @param string $type
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

    // Get the values from RegExp.
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

  /**
   * Create a function to ask the users for choice question.
   */
  private function choiceQuestion(string $userQuestion, array $options) {
    // Get input and output.
    $input = $this->input();
    $output = $this->output();

    $output->writeln('');
    // Ask to the user.
    $helper = new QuestionHelper();
    $question = new ChoiceQuestion(
      $userQuestion . PHP_EOL,
      $options,
    );

    $question->setErrorMessage('Invalid %s option.');
    $selected = $helper->ask($input, $output, $question);

    return $selected;
  }

  /**
   * General question.
   *
   * @param string $question
   * @return void
   */
  private function generalQuestion(string $question) {
    $helper = new QuestionHelper();
    $input = $this->input();
    $output = $this->output();

    // Set the question.
    $question = new Question(PHP_EOL . $question . PHP_EOL);

    // Get user response.
    $response = $helper->ask($input, $output, $question);

    return $response;
  }

  /**
   * Convert to camel case.
   *
   * @param string $input
   * @return void
   */
  private function convertToCamelCase(string $input) {
    $input = ucwords(strtolower($input));
    $input = str_replace(' ', '', $input);
    return $input;
  }

}
