# Deploy Magento site using Capistrano
#
# Usage:
# cap production deploy:setup
# cap production deploy

# --------------------------------------------
# Server Variables (Overridden)
#
# If your production store is located on a different
# or multiple servers consider using this syntax:
#
#    role :web, "www2.domain.com"
#    role :web, "www3.domain.com"
#    role :db, "master.domain.com", :primary => true
#    role :db, "slave1.domain.com"
#    role :db, "slave2.domain.com"
# --------------------------------------------
role :web,  '64.225.156.178'                     # APP01.Pandora
role :web,  '64.225.156.179'                     # APP02.Pandora
role :db,   '64.225.156.180', primary: true      # DB01.Pandora (64.225.156.180)
set :port,  22


set :db_remote_host, '192.168.0.3'

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
set :branch, "master"


# --------------------------------------------
# Magento Variables
# --------------------------------------------

set :shared_dir, 'shared_custom'

namespace :deploy do
  desc "Setup shared application directories and permissions after initial setup"
  task :setup_shared do
    # remove Capistrano specific directories
    try_sudo "rm -Rf #{shared_path}/log"
    try_sudo "rm -Rf #{shared_path}/pids"
    try_sudo "rm -Rf #{shared_path}/system"

    # symlink NFS shared directories
    %w(includes media sitemap var wp content).each do |dir|
      run "unlink #{shared_path}/#{dir}" if remote_dir_exists?("#{shared_path}/#{dir}")
      run "ln -s #{nfs_shared_path}/#{dir} #{shared_path}/#{dir}"
    end

    set_perms("#{nfs_shared_path}/*", 777)
  end

end
