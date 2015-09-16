# Deploy Magento site using Capistrano
#
# Usage:
# cap staging deploy:setup
# cap staging deploy


# --------------------------------------------
# Server Variables/Defaults
#
#    Alternative Server(s) Configuration:
#      role :web, "domain.com"  # can also use an IP address or alternative domain name
#      role :db, "domain.com"   # can also use an IP address or alternative domain name
# --------------------------------------------
server "107.170.21.252", :web, :db
set :port, 33322

set :db_remote_host, 'localhost'
set :phpfpm_init_command, 'service php5-fpm'


set :shared_dir, 'shared'

# --------------------------------------------
# Git/git-flow configuration
#
# if you are using Git or the git-flow workflow
# you should specify the :branch your environment
# deploys from.
#
# For example:
#   + the staging environment should use the `develop` branch
#   + the production environment should use the `master` branch
# --------------------------------------------
set :branch, "develop"

# set :branch, "feature/build-a-bracelet"


# --------------------------------------------
# Task chains
# --------------------------------------------
after "deploy", "watchdog"
