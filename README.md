FIRE

```
   )
  ) \
 / ) (
 \(_)/
```
=================

**F**ast
**I**nitialization and
**R**ebuilding of
**E**nvironments.

FIRE is a standardized set of commands to run local [Drupal](https://www.drupal.org/) environments, regardless of which Docker wrapper you use ([Lando](https://lando.dev/) or [DDEV](https://ddev.com/)), and regardless of where the live site is hosted ([Pantheon](https://pantheon.io/) or [Acquia](https://www.acquia.com/solutions/drupal-hosting)).  This means that every site that you work on will use the _same_ commands for things like getting a fresh database (`fire get-db`).  This makes it quicker and easier to get new people working on any of your sites.

# Install

1. Install the FIRE Launcher on your computer (Recommended but optional)\
https://github.com/fourkitchens/fire-launcher
2. Install the commands package into your project\
`composer require fourkitchens/fire --dev`
3. Create your `fire.yml` config file for the project\
`fire init`
4. Edit `fire.yml` and adjust the configuration.
5. Check if fire is working\
If you have installed the FIRE Launcher:
```
fire
```
  otherwise:
```
./vendor/bin/fire
```
  It should show you all the available FIRE commands.

6. Edit your project's README.md file and point to our [Project Documentation](./ProjectREADME.md)\
If your project has other requirements to run FIRE commands (e.g. does your project also require Node and NVM?), then be sure to also list those in your project's README.md.

# Usage

**With FIRE laucher installed:**

Example:

```
fire build
```

**Without the FIRE launcher installed:**

Example:
```
./vendor/bin/fire build
```

## Available commands:
  - `init`: Triggers a wizard that help you initialize the fire.yml file.

    alias: `i`

  - `env:start`: Starts the local Docker based env (lando, ddev).

    alias: `start`

  - `env:stop`: Stops the local Docker based env (lando, ddev).

    alias: `stop`

  - `env:poweroff`: Stops the local Docker and the proxy based env (lando, ddev).

    alias: `poweroff`

  - `env:switch`: A project can have both environments configured: Lando and DDev, so the "switch" command helps you to "hot swap" between local dev environments (Lando or DDev).

    alias: `env-switch|switch|sw`

  - `local:build`: Builds your Drupal Site from the scratch.

    Alias: `local-build, build`

    Options:

      `--no-db-import`: Ignores the database import process (Download & Import).

      `--no-db-download`: Ignores ONLY the DB download, data will be imported from your existing db backup file.

      `-f, --get-files`: Gets the Files from the remote server.

  - `local:build:drush-commands`: Drush Build commands - updb , cr, cim , cr, deploy:hook

      Alias `build-drush`

  - `local:build:js`: Builds Project JS Dependencies (Projects Root).

      Alias: `build-js`

  - `local:build:php`: Builds Project PHP Dependencies.

      Alias: `build-php`

  - `local:build:theme`: Builds Projects theme.

     Alias `build-theme`

  - `local:configure:export`: Exports sites configuration - none interaction required.

      Alias: `configure-export|configure_export|cex`

  - `local:configure:import`: Imports sites configuration - none interaction required.

      Alias: `configure-import|configure_import|cim`

  - `local:composer`: Composer proxy for local envs.

      Alias: `c, composer`

      Arguments:

        `args`: The composer command you would like to execute.

      Usage Example:

        fire composer install
        fire composer install -- --ignore-platform-reqs
        fire composer update drupal/core -- -W
        fire composer require 'drupal/devel:^5.1'
        fire composer require fourkitchens/fire -- --dev

  - `local:drush`: Drush proxy for local envs.

      Alias: `drush`

      Arguments:

      `args`: drush you would like to execute.

 - `local:get-db`: Get the database for local env.

    Alias: `get-db|db-get|getdb|dbget|get_db|db_get|local:db:get|local:get:db`

  - `local:get-files`: Downloads the sites files from the remote source (Pantheon, acquia).

    Alias: `get-files|files-get|getfiles|filesget|get_files|files_get|pull-files|pull_files|local:file:get|local:get:files`

    Options:

     `--no-download`: Reuse your existing files copy in the reference folder and placing them in the files folder (Pantheon only).

  - `local:import-db`: Import database for local envs.

      Alias: `import-db|db-import|importdb|dbimport|import_db|db_import`

  - `local:lint:php`: Runs and configure Phpcs for you local env.

    Alias: `phpcs`

  - `local:setup`: Setups your project from scratch (lando, ddev), all your data will be destroy and rebuild.

    Alias: `setup`

    Options:

      `--no-db-import`: Ignores the database import process (Download & Import).

      `--no-db-download`: Ignores ONLY the DB download, data will be imported from your existing db backup file.

      `-f, --get-files`: Gets the Files from the remote server.

  - `xdebug:enable`: Configures your local envs Xdebug to work with your prefered Code editor.

    Alias: `xd-en`

  - `vrt:generate-backstop-config`: Creates a basic Backstop.json for you.

    Alias: `vgc`

  - `vrt:init`: Configure your local enviroment from scratch to use VRT testing.

    Alias: `vinit`

  - `vrt:local-env-config`: Alters your local enviroment so you can use backstop.

    Alias: `vlec`

  - `vrt:reference`: Takes new reference screeshots from the reference URL.

    Alias: `vref`

  - `vrt:run`: Runs your VRT testing.

    Alias: `vrun`

  - `vrt:testing-setup`: Setups the Testing and reference sites for VRT testing.
    Alias: `vts`

  - `command:add`: This command allow you to create a custom command in your project.

    Alias: `ca, cadd`

  - `command:overwrite`: This command allow you to overwrite the default fire command in your project.

    Alias: `co, coverride`

    There are two types of overwrite:

      **1. Partial:** It first runs the original command and then allows you to add new functionality.

      **2. Full:** It replaces all existing code and allows you to write the command from scratch.

    You can also create a new command, just choice the "Custom" option at the prompt when it ask you for for the command you want to overwrite, then respond to the questions, now a new command should have been created in the custom path, by default only a task is added to cleans the Drupal cache, but from this file, you can add your custom tasks.

## Configuration
Into your project root create a file called: `fire.yml` and iside of it speficify your global project settings.

If you need to override some of the global settings latter for a specific env you can create `fire.local.yml` and there override as many variables as you want.

#### Configuration variables:

- `local_environment` : **Optional setting**, the system will automatically detected your local env, currently available: ddev, lando.

- `local_fe_theme_name`: **Optional setting**, the system will try to automatically get your theme, but you can always specify the theme you require to use.

- `local_theme_build_script`: NPM script you are using to build your theme.

- `remote_platform`: Your Sites Remote platform. Currently available: pantheon, acquia

- `remote_sitename`: Remote plaform Sites machine name.

- `remote_canonical_env`: Remote platform canonical env (The env to pull files and database from).


# Development

- Install the FIRE package from the source.
```
composer require fourkitchens/fire --dev --prefer-install=source
```

## Passing Arguments wrapped commands (i.e drush)
When using Fire to run a wrapped command like Drush, you can indeed pass arguments by using a double hyphen (--). This signals that all subsequent parameters should be treated as arguments for the wrapped command. Here's how you can structure it:

`fire drush cex -- -y`

## Dev backgroud

We are using [Robo](https://robo.li/) as Framework to develop this tool.

- Commmands examples: https://github.com/consolidation/robo/blob/4.x/examples/src/Robo/Plugin/Commands/ExampleCommands.php

- Robo as Framework Documentation: https://robo.li/docs/framework.html
