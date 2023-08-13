FIRE
=================

Fast Initialization and Rebuilding of Enviornments.

### Install:
`composer require fourkitchens/fire`


### Dev backgroud

We are using [Robo](https://robo.li/) as Framework to develop this tool.

- Commmands examples: https://github.com/consolidation/robo/blob/4.x/examples/src/Robo/Plugin/Commands/ExampleCommands.php

- Robo as Framework Documentation: https://robo.li/docs/framework.html


### Available commands:

- local:build:js          [build-js] Builds Project JS Dependencies (Projects Root).
- local:build:php         [build-php] Builds Project PHP Dependencies.
- local:configure:export  [configure-export|configure_export|cex] Export config.
- local:configure:import  [configure-import|configure_import|cim] Import config.
- local:drush             [drush] Drush proxy for local envs.
- local:get-db            [get-db|db-get|getdb|dbget|get_db|db_get|local:db:get|local:get:db] Import database for local envs.
- local:import-db         [import-db|db-import|importdb|dbimport|import_db|db_import] Import database for - local envs.
- local:build:theme       [build-theme] Builds Projects theme.
Example: `fire drush -- uli`


### Configuration:
Into your project root create a file called: `fire.yml` and iside of it speficify your configuration.

```
# You local env, currently available: acquia, lando
local_environment: lando
local_fe_theme_name: my_theme
local_theme_build_script: build
remote_platform: pantheon
remote_sitename: project-name
remote_canonical_env: live
```
