# Installing application

## Prerequisites

This application will properly work only with bare git repositories (created or 
cloned with `--bare` switch).

Application expects next directory structure:

```
/<repository root>
  |- group1
  |  |- repo1.git
  |  |- repo1.git.json # optional
  |  |- repo2.git
  |  |- repo2.git.json # optional
  .....
  |- groupN
     |- repoN.git
     |- repoN.git.json # optional
```

First level folders will be displayed as repository groups; second level
should contain folders with bare git repositories (with `.git` suffix),
and optional JSON files with additional information about repository.
That JSON files are using only by application, git itself does not need them.

Application receives its configuration via environment variables:

- `GITKIT_REPOSITORIES_ROOT` - folder that contains tree described above. 
  Application should be allowed to read and write files in this directory.
- `GITKIT_GIT_BINARY` - path to `git` binary, defaults to `git`.
- `GITKIT_GIT_USER` - path to `git` user (works together with accessing repositories via SSH).
- `GITKIT_SSH_KEYGEN_BINARY` - path to `ssh-keygen` binary, defaults to `ssh-keygen`.  
- `GITKIT_SSH_AUTHORIZED_KEYS` - path to `authorized_keys` file that will be used to access repositories via SSH.
  Application should be allowed to read and write this file, otherwise view/edit SSH keys will
  not work. 
  
## Requirements

Required dependencies:

- any web server (Nginx, Apache, etc.);
- PHP 7 (as FastCGI or apache module);
- Composer, NodeJS 8, NPM - to build application before running;
- GIT.

Optional dependencies (may be needed to enable extra features - 
read this document for more information):

- SSH server (OpenSSH, etc.);
- SSH-Keygen utility;   
- FCGIWrap;

## Preparing sources

Navigate to folder where you placed sources of the application, and then run:

```bash
$ composer install
$ npm install
$ npm run build
```

## Setting up web-server

### If you're using Nginx:

- edit `nginx.conf` file. It contains a set of options that should be changed 
  before running app, like host, path to repositories, etc.  
- navigate to `/etc/nginx/sites-enabled` folder and create a symlink to `nginf.conf`
  edited in previous step.
- restart Nginx to apply changes: `sudo service nginx restart`.

Now you can open your browser and use host specified in `nginx.conf` to open the app.  

### If you're using Apache:

**TODO:** write instructions.          

# Configuring access to repositories via HTTP

To allow accessing repositories via HTTP, git provides CGI utility called `git-http-backend`.
It relies on next variables:

- `GIT_PROJECT_ROOT` - path to folder with repositories; 
- `PATH_INFO` - path to repository (relative to `GIT_PROJECT_ROOT`);
- `REMOTE_USER` - remote username (provided with HTTP Basic auth);
- `REMOTE_ADDR` - remote user IP;
- `CONTENT_TYPE` - HTTP `Content-Type` header;
- `QUERY_STRING` - URL query string;
- `REQUEST_METHOD` - HTTP request method.

Web servers usually will provide all needed variables, except of first two, which 
will need additional set up.

## Setting up web-server

### If you're using Nginx: 

Nginx cannot run CGI scripts directly. Therefore we need to use `fcgiwrap` - 
a bridge between FastCGI and CGI interfaces. Install it:
```bash
$ sudo apt-get install fcgiwrap
```

Application is shipped with a sample Nginx configuration file: `nginx.conf`,
that already contains section to run `git-http-backend` via `fcgiwrap`. So
just check all configuration variables (they should be already set while installing
application), and restart Nginx if needed. 
   
### If you're using Apache:

**TODO:** write instructions.          

# Configuring access to repositories via SSH

To allow git access via ssh, we need to do next steps:

- Create user called `git` with restricted shell (`git-shell`)
- Create necessary folders; `/home/git/.ssh/authorized_keys` will
later contain list of ssh keys allowed to access repos.

All repositories then should be stored in `git` users home directory;
restricted shell will not allow to access other places. Each
repository can be accessed via path relative to that home directory.
For example, if we'll create repository: `sudo -u git git init --bare ./test.git`
it will be available via url: `git@host:test.git`, where part before `@`
is username (`git`) and `test.git` is relative path to its home directory.

## If you're using Debian-based distribution: 

- Install `git`: `sudo apt-get install git`
- Check if `git-shell` is available: `which git-shell`
- Create user: `sudo useradd --create-home --home-dir /home/git --password 1111 --shell $(which git-shell) git`
- Create directories: 
```bash
cd /home/git
sudo -u git mkdir git-shell-commands
sudo -u git mkdir .ssh
sudo -u git touch .ssh/authorized_keys
```
- Update application configuration (see section about setting up web-server):
```
GITKIT_REPOSITORIES_ROOT=/home/git
GITKIT_GIT_USER=git
GITKIT_SSH_AUTHORIZED_KEYS=/home/git/.ssh/authorized_keys
```

Do some preparations over web server and git:

- Add write permissions for group to `git` home directory: `sudo chmod -R g+rw /home/git`
- Find name and group of your web server user (usually `www-data:www-data`), and then cross-add it and `git` user to
each other groups:
```
sudo usermod --append --groups git www-data
sudo usermod --append --groups www-data git
```
It will allow application to create, edit and delete repositories and ssh keys.

By default, ssh server will require specific permission to folders:
```
rwx------ /home/<user>
rwxr-xr-x /home/<user>/.ssh
rw-r--r-- /home/<user>/.ssh/authorized_keys
```
Since we changed permissions to `git` home directory, you may also need to change ssh server config.
For `openssh-server` edit `/etc/ssh/sshd_config` and set this option: `StrictModes: no`.
