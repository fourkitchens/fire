# AGENTS

## Entrypoint
- CLI entrypoint is `bin/fire` (executes `bin/fire.php`); it requires Composer autoload to exist, so run `composer install` before local runs.

## Config + discovery
- `fire.yml` and optional `fire.local.yml` are read from the project root (four directories above `vendor/fourkitchens/fire`); if missing, only `InitCommand.php` is registered and the CLI prompts to run `fire init`.
- Local env auto-detection sets `local_environment` based on `.lando.yml` or `.ddev/config.yaml` in the project root.

## Command sources
- Core commands live in `src/Robo/Plugin/Commands/*Command.php` and are discovered via Robo `CommandFileDiscovery`.
- Project-level custom commands are loaded from `fire/src/Commands` in the project root (path is computed by stripping `vendor/fourkitchens/` from the package path).

## Config template
- `fire init` uses the template at `assets/templates/fire.yml`; update it if you change required config fields.
