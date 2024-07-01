<?php

namespace Fire\Robo\Plugin\Commands;

use Consolidation\AnnotatedCommand\CommandFileDiscovery;
use Fire\Robo\Plugin\Commands\FireCommandBase;
use Robo\Collection\CollectionBuilder;
use Robo\Symfony\ConsoleIO;
use Symfony\Component\Filesystem\Exception\IOException;

/**
 * Provides a command to overwrite others command.
 */
class CmdCustomCommand extends FireCommandBase {

  /**
   * Add Autoload to the composer file.
   *
   * @param CollectionBuilder $tasks
   * @param string $env
   * @param string $commandPath
   * @return void
   */
  protected function composerAutoload(CollectionBuilder &$tasks, string $env, string $namespace, string $src) {
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
      // Update composer.json file.
      file_put_contents($composerPath, json_encode($composerJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    // Re-import the autoload.
    $tasks->addTask($this->taskExec("$env composer dump-autoload"));
  
    return TRUE;
  }

  /**
   * Create custom path.
   *
   * @param string $commandPath
   * @return void
   */
  protected function createCustomDirectory(string $commandPath) {
    $filesystem = $this->taskFilesystemStack();
    $commandPath = $this->getLocalEnvRoot() . '/'  . $commandPath;

    if (!file_exists($commandPath)) {
      $filesystem->mkdir($commandPath, 0755)->run();
      $this->say('The path has been created.');
    } else {
      $this->say('The path already exists.');
    }
  }

  /**
   * Create the custom command.
   */
  protected function askOverwriteCommand(ConsoleIO $io) {
    $currentPath = __DIR__;
    $discovery = new CommandFileDiscovery();
    $discovery->setSearchPattern('*Command.php');
    $commandClasses = $discovery->discover($currentPath);

    $commands = [];
    foreach ($commandClasses as $cmdName) {
      $key = str_replace('Command', '', $cmdName);

      if (in_array($cmdName, ['CmdAddCommand', 'CmdCustomCommand', 'CmdOverwriteCommand'])) {
        continue;
      }

      $commands[$key] = $cmdName;
    }
    asort($commands);

    // Ask to the user for the command to overwrite.
    $selectedCommand = $io->choice('Select a command to overwrite:', array_keys($commands));

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
  protected function overwriteExistingCommand(ConsoleIO $io, string $namespace, string $commandPath, string $selectedCommand) {
    $currentPath = __DIR__;
    $filesystem = $this->taskFilesystemStack();
    $envRoot = $this->getLocalEnvRoot();
    $cmdFile = "{$selectedCommand}.php";
    $cmdCustomFile = "{$commandPath}/Custom{$cmdFile}";
    $origin = "{$currentPath}/{$cmdFile}";
    $destination = "{$envRoot}/{$cmdCustomFile}";
    $writeFile = TRUE;

    if (!file_exists($origin)) {
      $this->say("Could not locate file '$origin'.");
      return NULL;
    }
    elseif (file_exists($destination))  {
      $response = $io->confirm("The '$selectedCommand' command has already been overwritten previously. Would you like to replace it?", TRUE);

      if ($response) {
        $filesystem->remove($destination)->run();
      }
      else {
        $writeFile = FALSE;
      }
    }

    if ($writeFile) {
      $selectedType = $io->choice('Select the type of overwrite you want to use:', ['Full', 'Partial']);

      $this->say('');
      $filesystem->copy($origin, $destination, TRUE)->run();
      $this->say("The '$selectedCommand' command was successfully overwritten.");
      $this->updateCustomCommand($namespace, $destination, $selectedType);
    }

    $this->say('');
    $this->say('You can edit it with the following command:');
    $this->say('');
    $this->say("  $ code $cmdCustomFile");
    $this->say('');
  }

  /**
   * This function creates new command.
   *
   * @return void
   */
  protected function createCustomCommand(ConsoleIO $io, $namespace, $commandPath) {
    $filesystem = $this->taskFilesystemStack();
    $envRoot = $this->getLocalEnvRoot();
    $origin = dirname(__DIR__, 4) . '/assets/templates/CustomCommand.php';

    // Show message to the user.
    $this->say('Creating a new command.');

    // Prompt the user for information about the new command.
    $commandName = $io->ask('Enter the name of the new command:');
    $commandAlias = $io->ask('Enter the alias of the new command:');
    $commandDescription = $io->ask('Enter a description for the new command:');

    // Set variables.
    $commandNamespace = "{$namespace}Commands";
    $commandName = $this->convertToCamelCase($commandName);
    $commandFunction = lcfirst($commandName);
    $commandFire = strtolower($commandFunction);
    $commandFireFull = "custom:{$commandFire}";
    $commandName = "Custom{$commandName}Command";
    $commandFile = "{$commandPath}/{$commandName}.php";
    $destination = "{$envRoot}/{$commandFile}";

    $writeFile = TRUE;
    if (file_exists($destination))  {
      $response = $io->confirm("The '$commandName' command has already exist. Would you like to replace it?", TRUE);

      if ($response) {
        $filesystem->remove($destination)->run();
      }
      else {
        $writeFile = FALSE;
      }
    }

    if ($writeFile) {
      // Copy the new command template.
      $filesystem->copy($origin, $destination, TRUE)->run();

      // Replace the tokens.
      $this->taskReplaceInFile($destination)
        ->from([
          '<namespace>',
          '<commandName>',
          '<commandDescription>',
          '<commandAlias>',
          '<commandFunction>',
          '<commandFire>',
          '<commandFireFull>',
        ])
        ->to([
          $commandNamespace,
          $commandName,
          $commandDescription,
          $commandAlias,
          $commandFunction,
          $commandFire,
          $commandFireFull,
        ])
        ->run();
    }

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
  private function updateCustomCommand(string $namespace, string $filePath, $type) {
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
    $filesystem = $this->taskFilesystemStack();
    try {
      $filesystem->dumpFile($filePath, $newFileContent)->run();
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
