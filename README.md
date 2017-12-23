# gitkit

## Installation

### Install and configure `git` server

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

#### Instructions for Debian-based distributions 

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

### Install and configure app

Do some preparations over web-server and git:
- Add write permissions for group to `git` home directory: `sudo chmod -R g+w /home/git`
- Find name and group of your webserver user (usually `www-data:www-data`), and then cross-add it and `git` user to
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

Configure application:

- Edit `src/config/default.json`, change `repositories` setting to `git` users home directory path
(or put another path where do you store your repositories). Edit `repositories_owner` if needed.

### (To be continued)
