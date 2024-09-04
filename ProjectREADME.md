This project uses the [FIRE](https://github.com/fourkitchens/fire) toolkit for local development.

# How to set up this site on your local machine
1. Checkout the project's Git repository on your local machine (FIRE is ambivalent about where your Git repository is hosted).
2. Install the FIRE Launcher on your computer (Recommended but optional)\
   https://github.com/fourkitchens/fire-launcher \
If you don't do this, then where any commands below say `fire`, instead call the executable within your project.\
e.g. `./vendor/bin/fire`
3. `composer install` to get the vendor code (including Fire) added to your machine.

# Install (or reinstall) your project locally
```
fire build
```
This will call many other FIRE commands in the ideal order to get you up and running (`build-php`, `build-js`, `build-theme`, `get-db`, `import-db`, etc.).  If any one of these commands fails, you can try to re-run it with more verbose output to get more details `fire <command> -v`.

# To see a list of all available commands
Just run `fire`

# Get more info about a specific command
`fire help <command>`

# Most common commands.
Here we show the short-form alias.  Each of these also has a long-form full name.  Some of these commands accept additional arguments.
* `fire start`
* `fire stop`
* `fire build`
* `fire build-php`
* `fire build-theme`
* `fire get-db`
* `fire import-db`
* `fire drush`
* `fire cex`
* `fire cim`
* `fire xdebug:enable`

# More Info
See the [FIRE project page](https://github.com/fourkitchens/fire).
