FIRE

```
   )
  ) \
 / ) (
 \(_)/
```
=================

Fast Initialization and Rebuilding of Environments.

### Install:

**Install the fire launcher in your computer (Recommended but optional)**:

Follow this link for more instructions: https://github.com/fourkitchens/fire-launcher

**Install the commands package into your project:**

`composer require fourkitchens/fire --dev`

**Create your `file.yml` config file for the project**

example:
```
# NPM script you are using to build your theme.
local_theme_build_script: build
# Your Sites Remote platform. Currently available: pantheon, acquia
remote_platform: pantheon
# Remote plaform Sites machine name.
remote_sitename: project-name
# Remote platform canonical env (The env to pull files and database from).
remote_canonical_env: live
```

**Check if fire is working**

run the following command (If you have installed the fire launcher):

```
fire
```
It should show you all the available fire commands.
### Usage:

**With Fire laucher installed:**

Example:

```
fire build
```

**Without the fire launcher installed:**

Example:
```
./vendor/bin/fire build
```

### Available commands:
- env:start               [start] Starts the local Docker based env (lando, ddev).
- env:stop                [stop] Stops the local Docker based env (lando, ddev).
- local:build:js          [build-js] Builds Project JS Dependencies (Projects Root).
- local:build:php         [build-php] Builds Project PHP Dependencies.
- local:configure:export  [configure-export|configure_export|cex] Export config.
- local:configure:import  [configure-import|configure_import|cim] Import config.
- local:drush             [drush] Drush proxy for local envs.
- local:get-db            [get-db|db-get|getdb|dbget|get_db|db_get|local:db:get|local:get:db] Get the database from remote site.
- local:get-files         [get-files|files-get|getfiles|filesget|get_files|files_get|pull-files|pull_files|local:files:get|local:get:files] Get files from remote site and put in the local env.
- local:import-db         [import-db|db-import|importdb|dbimport|import_db|db_import] Import database for - local envs.
- local:build:theme       [build-theme] Builds Projects theme.
- local:build             [local-build|build] Builds your Drupal Site from the scratch.
- local:build:drush-commands  [build-drush] Drush Build commands - updb , cr, cim , cr, deploy:hook
- local:setup             [setup] Setups your project from scratch (lando, ddev), all your data will be destroy and rebuild.
- xdebug:enable           [xd-en] Configures your local envs Xdebug to work with your prefered Code editor.


### Configuration:
Into your project root create a file called: `fire.yml` and iside of it speficify your global project settings.

If you need to override some of the global settings latter for a specific env you can create `fire.local.yml` and there override as many variables as you want.

#### Configuration variables:

- `local_environment` : **Optional setting**, the system will automatically detected your local env, currently available: acquia, lando.

- `local_fe_theme_name`: **Optional setting**, the system will try to automatically get your theme, but you can always specify the theme you require to use.

- `local_theme_build_script`: NPM script you are using to build your theme.

- `remote_platform`: Your Sites Remote platform. Currently available: pantheon, acquia

- `remote_sitename`: Remote plaform Sites machine name.

- `remote_canonical_env`: Remote platform canonical env (The env to pull files and database from).


### Development

- Install the fire package from the source.
```
composer require fourkitchens/fire --dev --prefer-install=source
```
#### Dev backgroud

We are using [Robo](https://robo.li/) as Framework to develop this tool.

- Commmands examples: https://github.com/consolidation/robo/blob/4.x/examples/src/Robo/Plugin/Commands/ExampleCommands.php

- Robo as Framework Documentation: https://robo.li/docs/framework.html
