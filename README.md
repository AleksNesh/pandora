# Padoramoa.com (Pandora Charms at MOA)

## Git Flow

This project makes use of the [git-flow](http://nvie.com/posts/a-successful-git-branching-model/) branching model. Please make sure you are familiar with __git-flow__ before you continue work on this project. For the best introduction to getting started with __git-flow__, please read [Jeff Kreeftmeijer's blog post](http://jeffkreeftmeijer.com/2010/why-arent-you-using-git-flow/).

### Installing git-flow

#### Mac OS

If you are using a Mac, you should be using [homebrew](http://github.com/mxcl/homebrew) and setup is simple:

```bash
$ brew install git-flow
```

#### Linux

For Linux users, the easiest way to install is using Rick Osborne's __git-flow__ installer script. Run the following command:

```bash
$ wget --no-check-certificate -q -O - https://github.com/nvie/gitflow/raw/develop/contrib/gitflow-installer.sh | sudo bash
```

#### Windows

Please don't do web development on a Windows machine. :)
For help, check out the [git-flow](https://github.com/nvie/gitflow) Github repository for additional instructions.

### Shell Integration

For users of the [Bash](http://www.gnu.org/software/bash/) or [ZSH](http://www.zsh.org/) shells, there is some great work on the [git-flow-completion](http://github.com/bobthecow/git-flow-completion) project by __bobthecow__. It offers tab-completion for all __git-flow__ sub-commands and branch names.

## Project Setup

To get started working on __PandoraMOA.com__, first clone the development repository:

```bash
$ git clone -b develop git@github.com:pandoramoa.com/pandoramoa.com.git
```

This will check out the default __develop__ branch. This branch is used for current development and staging environments. To be able to deploy production releases you will need to also have a copy of the __master__ branch on your system. Grab and a tracking branch of __master__:

```bash
$ cd pandoramoa.com/
$ git branch --track master origin/master
```

Now that you have a local repository you have to make sure you initialize __git-flow__ as it isn't done for you. Make sure to select the default values at the prompts:

```bash
$ git flow init
```

You should now be all ready to start creating new features.

## SASS (Bourbon/Neat/Bitters)

### UPDATE:

The project now uses the [**Bundler** ruby gem](http://bundler.io/) to manage gem dependencies for this project while doing local development. You'll need to install the `bundler` gem like so:

```bash
$ gem install bundler
```

The bundler gem will use the **Gemfile** in this project's root directory to install and manage gems needed for the local development and deployment of this project.

**Run the following command to install all of the project's ruby gems required for the project:**

```bash
$ bundler install
```

**To update the installed gems after making modifications to the Gemfile:**

```bash
$ bundler update
```

Once you have ran the `bundle install` command you are ready to go.

The Build a Bracelet (Jewelry Designer) feature is an AngularJS app embedded within Magento so it won't follow all of the Magento conventions for fallback hierarchy or theming. The Build a Bracelet (Pan_JewelryDesigner) templates and theme files will be self-contained within the `app/code/local/Pan/JewelryDesigner` directory. The AngularJS app makes use of the [Bourbon](http://bourbon.io/), [Bourbon Neat](http://neat.bourbon.io/), and [Bitters](http://bitters.bourbon.io/) SASS libraries instead of raw CSS.

### UPDATE #2:

The Jewelry Designer feature now uses [Gulp](http://gulpjs.com/), [Node (and npm - the node package manager)](https://github.com/joyent/node/wiki/Installing-Node.js-via-package-manager), and [Bower](http://bower.io/) to manage the Angular app's modules, sass compiling and css/js concatenation and compacting. Once you have all three installed, you will need to go to `app/code/local/Pan/JewelryDesigner` and run `npm install` to install any necessary packages that the project relies upon.

Use [Gulp](http://gulpjs.com) to watch for SASS or Javascript file changes to keep your assets up-to-date.

```bash
# Example: sass --watch input-dir:output-dir
$ cd app/code/local/Pan/JewelryDesigner
$ npm install
$ gulp
```


## Local Development

Before you can run the site locally to test development, here are the basic steps required to run locally (this assumes local development on a Mac):

##### 1. Ensure you have a usable `.htaccess` file:

```bash
$ cp -f htaccess.dist .htaccess
```

##### 2. Change permissions on files/directories

```bash
$ chmod -R 777 var/ media/
$ chmod +x mage
```

##### 3. Setup your `hosts` file with a local development domain and add `127.0.0.1 pan.dev`

```bash
$ sudo vim /etc/hosts
```

##### 4. Create an Apache2 virtual host that will respond to `pan.dev`
##### 5. Reload your local webserver:

```bash
$ sudo apachectl graceful
```

##### 6. Setup cron

```bash
$ crontab -e
$ */6 * * * * /usr/bin/curl -s -o /dev/null http://pan.dev/cron.php
```

## Deployment

This project is deployed via a Ruby tool called [Capistrano (v2)](https://github.com/capistrano/capistrano). It also makes use of a custom extension provided by [capistrano-ash](https://github.com/augustash/capistrano-ash).

### Dependencies

These `gems` are required for deployment and must be installed prior to a successful deploy:

+ `capistrano-ash`

### Push Code Live

The command to push code to a specific environment is very easy and is a single line:

```bash
$ cap staging deploy
$ cap production deploy
```
