FROM ubuntu:xenial

# Install utilities
RUN apt-get update
RUN apt-get install --yes mc git openssh-server

# Fix openssh-server
RUN mkdir -p /var/run/sshd

## Check installed software
RUN git --version && (sshd -V || true)

## Add user `git` and create necessary files and directories
RUN useradd --skel /dev/null --create-home --home-dir /home/git --shell $(which git-shell) git
RUN cd /home/git
RUN mkdir git-shell-commands
RUN mkdir .ssh
RUN touch .ssh/authorized_keys
RUN chown -R git:git /home/git
RUN chmod -R g+rw /home/git

EXPOSE 22

ENTRYPOINT ["/app/docker/gitssh/entrypoint"]

CMD /usr/sbin/sshd -D -e
