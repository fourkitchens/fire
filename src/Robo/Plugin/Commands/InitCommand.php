<?php

namespace Fire\Robo\Plugin\Commands;

use Robo\Symfony\ConsoleIO;
use Symfony\Component\Yaml\Yaml;

/**
 * Provides a command to do fire intial setup.
 */
class InitCommand extends FireCommandBase {

  /**
   * Initialize fire configuration.
   *
   * Usage Example: fire init
   *
   * @command init
   * @aliases i
   * @usage fire init
   */
  public function init(ConsoleIO $io) {
    $firePath = $this->getVendorPath() . '/fourkitchens/fire';

    $io->say('Welcome to your FIRE setup wizard, This wizard will help you to create your fire.yml file.');

    $runInit = TRUE;
    if (file_exists($this->getLocalEnvRoot() . '/fire.yml')) {
      $runInit = $io->confirm('You already have a fire.yml file into your projects root folder, Do you want to continue and override it?', FALSE);
    }
    if ($runInit) {

      $wizardMode = $io->choice('Would you like run the step by step Configuration Wirzard or simply paste a fire.yml template file over your local enviroment root?', ['wizard', 'template'], 0);

      if ($wizardMode === 'wizard') {
        $remotePlatoformName = $io->choice('In which platform is your site being hosted?', ['pantheon', 'acquia']);
        $remoteSiteMachineName = $io->ask("What's your site machine name over the remote plaform?\n If your site is being host in pantheon you could use the CLI tool terminus to get that info with the command: terminus site:list");
        $remoteCanicalEnv = $io->ask("Which Remote enviroment you would like to use as your canonical source to setup your local enviroment?\n Tipically live (for pantheon) or prod (For acquia) are the best options");

        $themes = scandir($this->getDrupalRoot() . '/themes/custom/');
        foreach($themes as $key => $folder) {
          if ($folder === '.' || $folder === '..') {
            unset($themes[$key]);
          }
        }
        $themes = array_values($themes);
        if ($themes) {
          $localThemeName = $io->choice('Select your Default theme', $themes, 0);
        }
        else {
          $io->say('Please setup a theme for your site into this folder: ' . $this->getDrupalRoot() . '/themes/custom/');
        }

        $localThemeBuildScript = $io->ask('Please enter the name of theme building/compilation script for your theme, typically that script is called: build', 'build');

        $localThemeWatchScript = $io->ask('Please enter the name of theme watch script for your theme, typically that script is called: watch', 'watch');

        $defaultLocalEnv = FALSE;
        if (file_exists($this->getLocalEnvRoot() . '/.lando.yml') && file_exists($this->getLocalEnvRoot() . '/.ddev/config.yaml')) {
          $defaultLocalEnv = $io->choice('We have found your local environment is configured to use lando or ddev, which of those you would like as default for FIRE to use', ['lando', 'ddev']);
        }

        if (!file_exists($this->getLocalEnvRoot() . '/fire.yml')) {
          $this->taskFilesystemStack()->copy($firePath . '/assets/templates/fire.yml', $this->getLocalEnvRoot() . '/fire.yml')->run();
        }

        $fireConfig = Yaml::parse(file_get_contents($this->getLocalEnvRoot() . '/fire.yml'));
        if ($remotePlatoformName) {
          $fireConfig['remote_platform'] = $remotePlatoformName;
        }
        if ($remoteSiteMachineName) {
          $fireConfig['remote_sitename'] = $remoteSiteMachineName;
        }
        if ($remoteCanicalEnv) {
          $fireConfig['remote_canonical_env'] = $remoteCanicalEnv;
        }
        if ($localThemeBuildScript) {
          $fireConfig['local_theme_build_script'] = $localThemeBuildScript;
        }
        if ($localThemeWatchScript) {
          $fireConfig['local_theme_watch_script'] = $localThemeWatchScript;
        }
        if ($localThemeName) {
          $fireConfig['localThemeName'] = $localThemeName;
        }
        if ($defaultLocalEnv) {
          $fireConfig['local_environment'] = $defaultLocalEnv;
        }

        $fireYamlDump = Yaml::dump($fireConfig, 5, 2);
        file_put_contents($this->getLocalEnvRoot() . '/fire.yml', $fireYamlDump);
      }
      else {
        $this->taskFilesystemStack()->copy($firePath . '/assets/templates/fire.yml', $this->getLocalEnvRoot() . '/fire.yml', TRUE)->run();

      }
      // Adding new lines to .gitignore,
        $this->taskWriteToFile($this->getLocalEnvRoot() . '/.gitignore')
        ->textFromFile($this->getLocalEnvRoot() . '/.gitignore')
        ->appendUnlessMatches('/fire\.local\.yml/', "fire.local.yml\n")
        ->run();

      $io->say("We have created a FIRE configuration file over this path:\n " . $this->getLocalEnvRoot() . '/fire.yml' . "\n feel free to do any ajustments over that file as you need.");
    }
  }

}
