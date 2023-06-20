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

- local:drush (drush): Provides a proxy command for drush CLI.
Example: `fire drush -- uli`


### Configuration:
Into your project root create a file called: `fire.yml` and iside of it speficify your configuration.

```
{
  environment: lando,
  platform: pantheon,
  sitename: project-name,
  siteenv: dev,
}
```
