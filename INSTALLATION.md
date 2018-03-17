# Installing application

The easiest (and recommended) way to run application is Docker. `Dockerfile` is
located in `build/alpine/Dockerfile` folder, so you should specify it explicitly:
    
```bash
cd "<sources path>"
docker build --tag 'gitkit' --file './build/alpine/Dockerfile' .
docker run --tty --publish '7080:80' --publish '7022:22' 'gitkit'
```

If you want to deploy and run application manually - use scripts from `build/alpine` 
as an example.

## Keep your data after docker build

After docker image build, all data will be lost. To keep your data (or use 
existing repositories) use docker volumes.  

**Note**: this application will properly work only with bare git repositories (created or 
cloned with `--bare` switch).

Application expects next directory structure:

```
/<root>
  |- server_keys
     |- rsa
     |- rsa.pub
  |- authorized_keys
  |- repositories
     |- group1
     |  |- repo1.git
     |  |- repo2.git
     .....
     |- groupN
        |- repoN.git
```

**Important node**: all files and folders should be owned by single user.

`server_keys` folder contains set of server keys used by SSH daemon. Create
some keys with `ssh-keygen` command.

`authorized_keys` is a file with list of public keys allowed to connect to host.
Initially could be empty.

`repositories` is a folder with actually `git`  repositories. First level folders 
will be displayed as repository groups; second level should contain folders 
with bare git repositories (with `.git` suffix).

Use following commands to start docker image with volumes:

```bash
docker run --tty --publish '7080:80' --publish '7022:22' --volume '<path>:/var/lib/gitkit:rw' 'gitkit'
```

Or just use `docker-compose` (but check `docker-compose.yml` settings before building/running application).
