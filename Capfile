# Deploy Magento site using Capistrano
#
# Usage:
# cap <environment> deploy:setup
# cap <environment> deploy

# Required gems/libraries
require 'ash/magento'
require 'ash/wordpress_nested'
require 'ash/performance'

# --------------------------------------------
# Server Variables/Defaults
#
#    Alternative Server(s) Configuration:
#      role :web, "domain.com"  # can also use an IP address or alternative domain name
#      role :db, "domain.com"   # can also use an IP address or alternative domain name
# --------------------------------------------
set :user, "deploy"
set :application, "pandoramoa.com"
set(:deploy_to) { "/var/www/#{application}/#{stage}" }
set :group_writable, true

set :copy_exclude, [".git*", ".DS_Store", "*.sample", "LICENSE*", "Capfile",
    "RELEASE*", "*.rb", "config/deploy", "*.sql", "nbproject", "_template",
    'PATCH*', 'RELEASE_NOTES*', 'install.php', '.htaccess*', 'config/data/*.xls*',
    'bridge2cart' ]



set(:nfs_shared_path) { File.join(deploy_to, 'shared') }


# --------------------------------------------
# Magento Variables
# --------------------------------------------
set :enable_modules, %w(Cm_RedisSession)
set :disable_modules, %w(Ash_Bar Ash_Devbar)

# --------------------------------------------
# Git/SVN Variables
#
#    Example SVN configuration:
#      set :repository, "https://svn.augustash.com/<PATH/TO/REPO>/trunk"
#      set :scm_username, "<SVN_USER>"
#
#    Example Git configuration:
#      set :repository, "git@github.com:augustash/<REPO_NAME>.git"
#      set :scm, "git"
#      #set :branch, "master" # define which branch
#                             # should be used for deployments
# --------------------------------------------
set :repository, "git@github.com:pandoramoa/#{application}.git"
set :scm, "git"
# uncomment the line below if your project uses submodules (updates submodules)
#set :git_enable_submodules, 1

# --------------------------------------------
# Database/Backup Variables
# --------------------------------------------
set :keep_backups, 10 # only keep 3 backups (default is 10)
set :backup_exclude, %w(media var .git wp-content/cache wp-content/uploads)

set(:dbuser) { "#{stage}_user"}
set(:dbname) { "magento_#{stage}" }

# --------------------------------------------
# Compile Sass stylesheets and upload to servers
#
# ONLY RUNS IF YOU HAVE SET :compass_watched_dirs
#
# :compass_watched_dirs can either be a string or an array
#
# Example:
#   # Use the array syntax if you compass watch several directories:
#   set :compass_watched_dirs, ["skin/frontend/enterprise/ash", "skin/frontend/enterprise/my_custom_theme"]
#   # Use the string syntax if you only compass watch one directory:
#   set :compass_watched_dirs, "skin/frontend/enterprise/my_custom_theme"
#
# If you name your public stylesheets directory something
# other than "stylesheets" than you can specify the name of
# the directory with the :stylesheets_dir_name variable
#
# Examples (both assume your compiled stylesheets exist within your :compass_watched_dirs):
#   # If your Sass compiles to your assets/css directory
#   # (i.e., `skin/frontend/enterprise/my_custom_theme/assets/css`):
#   set :stylesheets_dir_name, "assets/css"
#   # If your Sass compiles to a separate directory
#   # (i.e., `skin/frontend/enterprise/my_custom_theme/styles`):
#   set :stylesheets_dir_name, "styles"
# --------------------------------------------
set :skip_compass_compile, true # change to true if you want to skip all compass-related tasks
# set :compass_watched_dirs, "skin/frontend/pan/pan"
set :compass_watched_dirs, nil

# leave it as expanded CSS for Magento CSS merging to work correctly
set :compass_env_override, 'development'
set :compass_output_override, 'expanded'

# set :sass_watched_dirs, "app/code/local/Pan/JewelryDesigner/app/css/sass"
# set :sass_output_dirs, "app/code/local/Pan/JewelryDesigner/app/css"
set :skip_sass_compile, true

set :gemfile_path, 'Gemfile'


# --------------------------------------------
# NGINX/PHP-FPM configuration
#
# If your web server is using NGINX with PHP-FPM
# you can use the below variables and tasks to
# start/stop/restart/status nginx and php-fpm
# processes. This is especially necessary if you
# are using something like APC to cache your code.
#
# If you are going use these commands it is highly
# suggested that you setup your SSH user to run
# common deploy-related commands (i.e., rm, chmod,
# rsync, etc.) as sudo w/o
# requiring a password.
#
# For example, in your `sudoers` file:
# ```bash
# # ....stuff...
#
# # Command Aliases
# Cmnd_Alias DEPLOYMENT = /usr/bin/rsync, /bin/chmod, /bin/rm, /etc/init.d/nginx, /etc/init.d/php5-fpm
# # ...more stuff...
#
# # The COMMANDS section may have other options added to it.
# #
# # Allow root to run any commands anywhere
# root    ALL=(ALL)       ALL
# <SSH_USER>  ALL=NOPASSWD:   DEPLOYMENT
# ```
#
# ---------
# Options:
# ---------
# :nginx_init_command (Default: `/etc/init.d/nginx`)
#   The path to your nginx control script. You could also use
#   `service nginx` but it's experimental at this point.
#
# :phpfpm_init_command (Default: `/etc/init.d/php5-fpm`)
#    The path to your php-fpm control script. You could also use
#   `service php5-fpm` but it's experimental at this point.
#
# --------------------------------------------
set :nginx_init_command, 'service nginx'
set :phpfpm_init_command, 'service php-fpm'


# --------------------------------------------
# Setting nested WordPress variable defaults
# --------------------------------------------

# ------
# Database Credentials
# assumes you're using the same database user, password and database
# as your main project would use; if you are not doing so, simply use
# the following template for defining the credentials:
#
#    Alternative Database Credential Configuration:
#      set :wp_db_user, "mycustomuser"
#      set :wp_db_name, "custom_wp_database"
#      set :wp_db_pass, proc{ Capistrano::CLI.password_prompt("Database password for '#{wp_db_user}':") }
# ------
set(:wp_db_user) { "#{dbuser}" }
set(:wp_db_name) { "wordpress_#{stage}" }
set(:wp_db_pass) { "#{dbpass}" }
set(:wp_db_host) { "#{db_remote_host}" }

# ------
# Multi-WordPress installations
# Create an array of configuration settings for
# each of the WordPress sites that should follow
# the following defined for each installation:
#
#    :wp_blogs       # array, contains hashes with each hash
#                    #   containing options for a given WordPress
#                    #   installation
#
#    :directory      # string, relative file path to the WordPress
#                    #   installation from the project's root directory
#
#    :db_prefix      # string, table prefix for all WordPress tables
#
#    :base_url       # hash, contains key/value pairs for environments and
#                    #   their full URLs
#                    #   NOTE: DO NOT INCLUDE the trailing slash "/" in the URL
#
# The following is an example of a well formed and
# valid configuration array for multiple WordPress
# installations
#
#    set :wp_blogs, [
#       { :directory => "blog1", :db_prefix => "wp1_",
#         :base_url => {
#           :staging => "http://staging.example.com",
#           :production => "http://www.example.com"
#         }
#       },
#       { :directory => "blog2", :db_prefix => "wp2_",
#         :base_url => {
#           :staging => "http://staging.anotherexample.com",
#           :production => "http://www.anotherexample.com"
#         }
#       }
#     ]
# ------
set :wp_blogs, [
  { :directory => "wp", :db_prefix => "wp_",
    :base_url => {
      :staging => "http://staging.pandoramoa.com",
      :production => "http://www.pandoramoa.com"
    }
  }
]

# --------------------------------------------
# Task chains
# --------------------------------------------
before "deploy:update_code", "backup"
after "magento:symlink", "wordpress:nested"
after "deploy", "nginx:reload"

# after "compass", "sass"


# --------------------------------------------
# Overloaded tasks
# --------------------------------------------

# --------------------------------------------
# Ash tasks
# --------------------------------------------
namespace :ash do
  desc "Set standard permissions for Ash servers"
  task :fixperms, :roles => :web, :except => { :no_release => true } do
    # try to change group to 'deploy' user (fix issues with var/cache/* files owned by 'memcached')
    run "#{try_sudo} chown -R #{user}:#{user} #{latest_release}" if remote_dir_exists?(latest_release)

    # chmod the files and directories.
    set_perms_dirs("#{latest_release}")
    set_perms_files("#{latest_release}")
  end
end

namespace :seo do
  desc "Override seo:robots to keep existing robots.txt file"
  task :robots, :roles => :web do
    logger.info "Skipping generation of robots.txt file b/c project has a good one."
  end
end

namespace :deploy do
  desc <<-DESC
    Prepares one or more servers for deployment. Before you can use any \
    of the Capistrano deployment tasks with your project, you will need to \
    make sure all of your servers have been prepared with `cap deploy:setup'. When \
    you add a new server to your cluster, you can easily run the setup task \
    on just that server by specifying the HOSTS environment variable:

      $ cap HOSTS=new.server.com deploy:setup

    It is safe to run this task on servers that have already been set up; it \
    will not destroy any deployed revisions or data.
  DESC
  task :setup, :except => { :no_release => true } do
    dirs = [deploy_to, releases_path, nfs_shared_path]
    dirs += shared_children.map { |d| File.join(shared_path, d.split('/').last) }
    run "mkdir -p #{dirs.join(' ')}"
    run "#{try_sudo} chmod g+w #{dirs.join(' ')}" if fetch(:group_writable, true)
    # run "chmod 755 #{dirs.join(' ')}" if fetch(:group_writable, true)
  end

  desc <<-DESC
    Updates the symlink to the most recently deployed version. Capistrano works \
    by putting each new release of your application in its own directory. When \
    you deploy a new version, this task's job is to update the `current' symlink \
    to point at the new version. You will rarely need to call this task \
    directly; instead, use the `deploy' task (which performs a complete \
    deploy, including `restart') or the 'update' task (which does everything \
    except `restart').

    AAI OVERRIDES:
    removes use of try_sudo with symlink command because we use try_sudo \
    (set :use_sudo, true) for several common deploy-related tasks, but symlinks \
    are not part of the tasks that truly require sudo privileges
  DESC
  task :create_symlink, :except => { :no_release => true } do
    on_rollback do
      if previous_release
        run "#{try_sudo} rm -f #{current_path}; ln -s #{previous_release} #{current_path}; true"
      else
        logger.important "no previous release to rollback to, rollback of symlink skipped"
      end
    end

    run "#{try_sudo} rm -f #{current_path} && ln -s #{latest_release} #{current_path}"
  end

  desc <<-DESC
    Clean up old releases. By default, the last 5 releases are kept on each \
    server (though you can change this with the keep_releases variable). All \
    other deployed revisions are removed from the servers. By default, this \
    will use sudo to clean up the old releases, but if sudo is not available \
    for your environment, set the :use_sudo variable to false instead. \

    OVERRIDES:
    + set/reset file and directory permissions
    + remove old releases per host instead of assuming the releases are \
      the same for every host

    see http://blog.perplexedlabs.com/2010/09/08/improved-deploycleanup-for-capistrano/
  DESC
  task :cleanup, :except => { :no_release => true } do
    count = fetch(:keep_releases, 5).to_i
    cmd = "ls -xt #{releases_path}"
    run cmd do |channel, stream, data|
      local_releases = data.split.reverse
      if count >= local_releases.length
        logger.important "no old releases to clean up on #{channel[:server]}"
      else
        logger.info "keeping #{count} of #{local_releases.length} deployed releases on #{channel[:server]}"

        directories = (local_releases - local_releases.last(count)).map { |release|
          File.join(releases_path, release)
        }.join(" ")

        directories.split(" ").each do |dir|
          begin
            # adding a chown -R method to fix permissions on the directory
            # this should help with issues related to permission denied
            # as in issues #28 and #30
            run "#{try_sudo} chown -R #{user}:#{user} #{dir}" if remote_dir_exists?(dir)

            set_perms_dirs(dir)
            set_perms_files(dir)
          rescue Exception => e
            logger.important e.message
            logger.info "Moving on to the next directory..."
          end
        end

        run "#{try_sudo} rm -rf #{directories}", :hosts => [channel[:server]]
      end
    end
  end

  namespace :rollback do
    desc <<-DESC
      [internal] Points the current symlink at the previous revision.
      This is called by the rollback sequence, and should rarely (if
      ever) need to be called directly.

      AAI OVERRIDES:
      removes use of try_sudo with symlink command because we use try_sudo \
      (set :use_sudo, true) for several common deploy-related tasks, but symlinks \
      are not part of the tasks that truly require sudo privileges
    DESC
    task :revision, :except => { :no_release => true } do
      if previous_release
        run "#{try_sudo} rm #{current_path}; ln -s #{previous_release} #{current_path}"
      else
        abort "could not rollback the code because there is no prior release"
      end
    end
  end
end


namespace :magento do
  desc "Symlink shared directories"
  task :symlink, :roles => :web, :except => { :no_release => true } do
    %w(includes media sitemap var content).each do |dir|
      run "ln -nfs #{nfs_shared_path}/#{dir} #{latest_release}/#{dir}"
    end
  end

  desc "Purge Magento cache directory"
  task :purge_cache, :roles => :db, :except => { :no_release => true } do
    logger.info "Changing ownership  of cache directories before removing cached files"

    run "#{try_sudo} chown -R #{user}:#{user} #{nfs_shared_path}/var/cache "
    run "#{try_sudo} chown -R #{user}:#{user} #{nfs_shared_path}/var/full_page_cache " if remote_dir_exists?("#{nfs_shared_path}/var/full_page_cache}")

    try_sudo "rm -Rf #{nfs_shared_path}/var/cache"
    try_sudo "rm -Rf #{nfs_shared_path}/var/full_page_cache" if remote_dir_exists?("#{nfs_shared_path}/var/full_page_cache}")
  end
end

namespace :wordpress do
  namespace :nested do
    desc "Setup shared folders for WordPress"
    task :setup_shared, :roles => :web do
      wp_blogs.each do |blog|
        wp_blog_directory = blog[:directory]

        # create shared directories
        run "mkdir -p #{nfs_shared_path}/#{wp_blog_directory}/uploads"
        run "mkdir -p #{nfs_shared_path}/#{wp_blog_directory}/cache"
        # set correct permissions
        run "#{sudo} chmod -R 777 #{nfs_shared_path}/#{wp_blog_directory}"
      end
    end

    desc "Links the correct settings file"
    task :symlink, :roles => :web, :except => { :no_release => true } do
      # internal call to the :prepare_for_symlink task
      wordpress.nested.prepare_for_symlink

      # symlink files/directories
      wp_blogs.each do |blog|
        wp_blog_directory = blog[:directory]
        wp_uploads_path   = "#{wp_blog_directory}/wp-content/uploads"
        wp_cache_path     = "#{wp_blog_directory}/wp-content/cache"

        run "ln -nfs #{nfs_shared_path}/#{wp_blog_directory}/uploads #{latest_release}/#{wp_uploads_path}"
        run "ln -nfs #{nfs_shared_path}/#{wp_blog_directory}/cache #{latest_release}/#{wp_cache_path}"
        run "ln -nfs #{latest_release}/#{wp_blog_directory}/wp-config.#{stage}.php #{latest_release}/#{wp_blog_directory}/wp-config.php"
      end
    end

    desc <<-DESC
      Set WordPress Base URL in database

      Overridden to account for Fishpig_Wordpress integration
      and difference between `siteurl` and `home`
    DESC
    task :updatedb, :roles => :db, :except => { :no_release => true } do
      servers = find_servers_for_task(current_task)
      servers.each do |server|
        wp_db_host = fetch(:wp_db_host, server.host)

        wp_blogs.each do |blog|
          wp_blog_directory   = blog[:directory]
          wp_db_prefix        = blog[:db_prefix]
          wp_base_url_prefix  = blog[:base_url]["#{stage}".to_sym]
          wp_base_url         = "#{wp_base_url_prefix}/#{wp_blog_directory}"

          # set WP site url to the base url prefix + '/' + directory name (e.g., 'http://www.pandoramoa.com/wp')
          run "mysql -h #{wp_db_host} -u #{wp_db_user} --password='#{wp_db_pass}' -e 'UPDATE #{wp_db_name}.#{wp_db_prefix}options SET option_value = \"#{wp_base_url}\"  WHERE option_name = \"siteurl\"'"

          # set WP home url to the base url prefix + '/' + url path to access it via Fishpig_Wordpress integration
          # (e.g., 'http://www.pandoramoa.com/blog')
          run "mysql -h #{wp_db_host} -u #{wp_db_user} --password='#{wp_db_pass}' -e 'UPDATE #{wp_db_name}.#{wp_db_prefix}options SET option_value = \"#{wp_base_url_prefix}/blog\" WHERE option_name = \"home\"'"
        end
      end
    end
  end
end

namespace :backup do
  desc <<-DESC
    Perform a backup of database files

    Overridden to include mysqldump of separate Wordpress database
  DESC
  task :db, :roles => :web do
    if previous_release
      mysqldump     = fetch(:mysqldump, "mysqldump")
      dump_options  = fetch(:dump_options, "--single-transaction --create-options --quick")
      dbhost        = fetch(:db_remote_host, 'localhost')

      puts "Backing up the database now and putting dump file in the previous release directory"

      # create the temporary copy for the release directory
      # which we'll tarball in the backup:web task
      run "mkdir -p #{tmp_backups_path}/#{release_name}"

      now = Time.now.to_s.gsub(/ /, "_")
      # ignored db tables
      ignore_tables = fetch(:ignore_tables, [])
      if !ignore_tables.empty?
        ignore_tables_str = ''
        ignore_tables.each{ |t| ignore_tables_str << "--ignore-table='#{dbname}'.'" + t + "' " }

        # define the filenames (include the current_path so the dump file will be within the directory)
        data_filename       = "#{tmp_backups_path}/#{release_name}/#{dbname}_data_dump-#{now}.sql.gz"
        structure_filename  = "#{tmp_backups_path}/#{release_name}/#{dbname}_structure_dump-#{now}.sql.gz"

        # dump the database structure for the proper environment
        run "#{mysqldump} --single-transaction --create-options --quick --triggers --routines --no-data -h #{dbhost} -u #{dbuser} -p #{dbname} | gzip -c --best > #{structure_filename}" do |ch, stream, out|
            ch.send_data "#{dbpass}\n" if out =~ /^Enter password:/
        end

        # dump the database data for the proper environment
        run "#{mysqldump} #{dump_options} -h #{dbhost} -u #{dbuser} -p #{dbname} #{ignore_tables_str} | gzip -c --best > #{data_filename}" do |ch, stream, out|
            ch.send_data "#{dbpass}\n" if out =~ /^Enter password:/
        end
      else
        # define the filename (include the current_path so the dump file will be within the directory)
        filename = "#{tmp_backups_path}/#{release_name}/#{dbname}_dump-#{now}.sql.gz"

        # dump the database for the proper environment
        run "#{mysqldump} #{dump_options} -h #{dbhost} -u #{dbuser} -p #{dbname} | gzip -c --best > #{filename}" do |ch, stream, out|
            ch.send_data "#{dbpass}\n" if out =~ /^Enter password:/
        end
      end

      #
      # Backup WordPress database
      #
      puts "Backing up the WordPress database now and putting dump file in the previous release directory"

      # define the filename (include the current_path so the dump file will be within the directory)
      wp_filename = "#{tmp_backups_path}/#{release_name}/#{wp_db_name}_dump-#{now}.sql.gz"

      # dump the database for the proper environment
      run "#{mysqldump} #{dump_options} -h #{wp_db_host} -u #{wp_db_user} -p #{wp_db_name} | gzip -c --best > #{wp_filename}" do |ch, stream, out|
          ch.send_data "#{wp_db_pass}\n" if out =~ /^Enter password:/
      end
    else
      logger.important "no previous release to backup to; backup of database skipped"
    end
  end
end


# Add support for compass compiling SASS stylesheets via bundler and the sass gem
namespace :sass do

  desc "Compile SASS stylesheets (via Bundler and Sass gems) and upload to remote server"
  task :default do
    # optional way to skip compiling of stylesheets and just upload them to the servers
    skip_sass_compile = fetch(:skip_sass_compile, false)

    sass.compile unless skip_sass_compile
    sass.upload_stylesheets
    ash.fixperms
  end


  desc 'Uploads compiled stylesheets to their matching watched directories'
  task :upload_stylesheets, :roles => :web, :except => { :no_release => true } do
    watched_dirs          = fetch(:sass_watched_dirs, nil)
    output_dirs           = fetch(:sass_output_dirs, nil)
    skip_compass_compile  = fetch(:skip_sass_compile, false)

    port                  = fetch(:port, 22)

    # finds all the web servers that we should upload stylesheets to
    servers = find_servers :roles => :web

    if !output_dirs.nil?
      if output_dirs.is_a? String
        logger.debug "Uploading compiled stylesheets for #{output_dirs}"
        logger.debug "trying to upload stylesheets from ./#{output_dirs}/ -> #{latest_release}/#{output_dirs}/"

        servers.each do |web_server|
          upload_command = "scp -r -P #{port} ./#{output_dirs}/*.css* #{user}@#{web_server}:#{latest_release}/#{output_dirs}/"

          logger.info "running SCP command:"
          logger.debug upload_command
          system(upload_command)
        end
      elsif output_dirs.is_a? Array
        logger.debug "Uploading compiled stylesheets for #{output_dirs.join(', ')}"
        output_dirs.each do |dir|
          logger.debug "trying to upload stylesheets from ./#{dir}/ -> #{latest_release}/#{dir}/"

          servers.each do |web_server|
            upload_command = "scp -r -P #{port} ./#{dir}/*.css* #{user}@#{web_server}:#{latest_release}/#{dir}/"

            logger.info "running SCP command:"
            logger.debug upload_command
            system(upload_command)
          end
        end
      else
        logger.debug "Unable to upload compiled stylesheets because :sass_watched_dirs was neither a String nor an Array"
      end
    else
      logger.info "Skipping uploading of compiled stylesheets `sass:upload` because `:sass_watched_dirs` wasn't set"
    end
  end

  desc 'Compile minified version of CSS assets using Compass gem'
  task :compile, :roles => :web, :except => { :no_release => true } do
    watched_dirs          = fetch(:sass_watched_dirs, nil)
    output_dirs           = fetch(:sass_output_dirs, nil)
    skip_compass_compile  = fetch(:skip_sass_compile, false)


    if !watched_dirs.nil? && skip_sass_compile == false
      sass_bin_local     = find_sass_bin_path
      sass_bin           = fetch(:sass_bin, sass_bin_local)
      sass_env           = fetch(:sass_env, "development")
      sass_output        = fetch(:sass_output, 'expanded') # nested, expanded, compact, compressed

      # Run compass compile commands via `bundle exec` if the
      # project has a Gemfile; otherwise fallback to the `compass`
      if (!gemfile_path.nil? && gemfile_path != false)
        bundler_bin_local     = find_bundler_bin_path
        bundler_bin           = fetch(:bundler_bin, bundler_bin_local)
        sass_exec_cmd      = "BUNDLE_GEMFILE=#{gemfile_path} #{bundler_bin} exec #{sass_bin}"
      elsif !sass_bin.nil?
        sass_exec_cmd = "#{sass_bin}"
      else
        sass_exec_cmd = nil
      end

      if !sass_exec_cmd.nil?
        if watched_dirs.is_a? String
          logger.debug "Compiling SASS for #{watched_dirs}"
          cmd = "#{sass_exec_cmd} --update ./#{watched_dirs}:./#{output_dirs}"
          logger.debug "Running compile command: \n\n#{cmd}\n\n"

          system "#{cmd}"
        elsif watched_dirs.is_a? Array
          logger.debug "Compiling SASS for #{watched_dirs.join(', ')}"

          watched_dirs.each_with_index do |dir, index|
            if output_dirs.is_a? Array
              output_dir = output_dirs[index]
            else
              output_dir = output_dirs
            end

            cmd = "#{sass_exec_cmd} --update ./#{dir}:./#{output_dir}"

            logger.debug "Running compile command: \n\n#{cmd}\n\n"
            system "#{cmd}"
          end
        else
          logger.debug "Unable to compile SASS because :compass_watched_dirs was neither a String nor an Array"
        end
      else
        logger.info "Skipping SASS compilation in `compass:compile` because unable to find the bin executable for the compass gem"
      end
    else
      logger.info "Skipping compass Sass compilation"
    end
  end

  desc "Finds the bin executable path for the sass gem"
  task :find_sass_bin_path, :except => { :no_release => true } do
    begin
      spec      = Gem::Specification.find_by_name("sass")
      gem_root  = spec.gem_dir
      gem_bin   = gem_root + "/bin/sass"
    rescue Gem::LoadError => e
      logger.debug "Unable to find the gem 'sass'! Check to see if it's installed: `gem list -d compass` or install: `gem install sass`"
      gem_bin = nil
    rescue Exception => e
      logger.debug "Unable to find the sass executable bin path because of this error: #{e.message}"
      gem_bin = nil
    end

    logger.debug "Path to sass executable: #{gem_bin.inspect}"

    # return the path the sass executable
    gem_bin
  end


  desc "Finds the bin executable path for the compass gem"
  task :find_bundler_bin_path, :except => { :no_release => true } do
    begin
      spec      = Gem::Specification.find_by_name("bundler")
      gem_root  = spec.gem_dir
      gem_bin   = gem_root + "/bin/bundle"
    rescue Gem::LoadError => e
      logger.debug "Unable to find the gem 'bundler'! Check to see if it's installed: `gem list -d bundler` or install: `gem install bundler`"
      gem_bin = nil
    rescue Exception => e
      logger.debug "Unable to find the bundler executable bin path because of this error: #{e.message}"
      gem_bin = nil
    end

    logger.debug "Path to bundler executable: #{gem_bin.inspect}"

    # return the path the bundler executable
    gem_bin
  end
end

# Add support for compass compiling via bundler
namespace :compass do
  desc 'Compile minified version of CSS assets using Compass gem'
  task :compile, :roles => :web, :except => { :no_release => true } do
    watched_dirs          = fetch(:compass_watched_dirs, nil)
    skip_compass_compile  = fetch(:skip_compass_compile, false)


    if !watched_dirs.nil? && skip_compass_compile == false
      compass_bin_local     = find_compass_bin_path
      compass_bin           = fetch(:compass_bin, compass_bin_local)
      compass_env           = fetch(:compass_env, "development")
      compass_output        = fetch(:compass_output, 'expanded') # nested, expanded, compact, compressed

      # Run compass compile commands via `bundle exec` if the
      # project has a Gemfile; otherwise fallback to the `compass`
      if (!gemfile_path.nil? && gemfile_path != false)
        bundler_bin_local     = find_bundler_bin_path
        bundler_bin           = fetch(:bundler_bin, bundler_bin_local)
        compass_exec_cmd      = "BUNDLE_GEMFILE=#{gemfile_path} #{bundler_bin} exec #{compass_bin}"
      elsif !compass_bin.nil?
        compass_exec_cmd = "#{compass_bin}"
      else
        compass_exec_cmd = nil
      end

      if !compass_exec_cmd.nil?
        if watched_dirs.is_a? String
          logger.debug "Compiling SASS for #{watched_dirs}"
          cmd = "#{compass_exec_cmd} clean ./#{watched_dirs} && #{compass_exec_cmd} compile --output-style #{compass_output_override} --environment #{compass_env_override} ./#{watched_dirs}"
          logger.debug "Running compile command: \n\n#{cmd}\n\n"

          system "#{cmd}"
        elsif watched_dirs.is_a? Array
          logger.debug "Compiling SASS for #{watched_dirs.join(', ')}"
          watched_dirs.each do |dir|
            cmd = "#{compass_exec_cmd} clean ./#{dir} && #{compass_exec_cmd} compile --output-style #{compass_output_override} --environment #{compass_env_override} ./#{dir}"

            logger.debug "Running compile command: \n\n#{cmd}\n\n"
            system "#{cmd}"
          end
        else
          logger.debug "Unable to compile SASS because :compass_watched_dirs was neither a String nor an Array"
        end
      else
        logger.info "Skipping SASS compilation in `compass:compile` because unable to find the bin executable for the compass gem"
      end
    else
      logger.info "Skipping compass Sass compilation"
    end
  end

  desc "Finds the bin executable path for the sass gem"
  task :find_sass_bin_path, :except => { :no_release => true } do
    begin
      spec      = Gem::Specification.find_by_name("sass")
      gem_root  = spec.gem_dir
      gem_bin   = gem_root + "/bin/sass"
    rescue Gem::LoadError => e
      logger.debug "Unable to find the gem 'sass'! Check to see if it's installed: `gem list -d compass` or install: `gem install sass`"
      gem_bin = nil
    rescue Exception => e
      logger.debug "Unable to find the sass executable bin path because of this error: #{e.message}"
      gem_bin = nil
    end

    logger.debug "Path to sass executable: #{gem_bin.inspect}"

    # return the path the sass executable
    gem_bin
  end


  desc "Finds the bin executable path for the compass gem"
  task :find_bundler_bin_path, :except => { :no_release => true } do
    begin
      spec      = Gem::Specification.find_by_name("bundler")
      gem_root  = spec.gem_dir
      gem_bin   = gem_root + "/bin/bundle"
    rescue Gem::LoadError => e
      logger.debug "Unable to find the gem 'bundler'! Check to see if it's installed: `gem list -d bundler` or install: `gem install bundler`"
      gem_bin = nil
    rescue Exception => e
      logger.debug "Unable to find the bundler executable bin path because of this error: #{e.message}"
      gem_bin = nil
    end

    logger.debug "Path to bundler executable: #{gem_bin.inspect}"

    # return the path the bundler executable
    gem_bin
  end
end
