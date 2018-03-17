#!/usr/bin/env ash

# global config variables
export SPAWN_FCGI_BINARY=${SPAWN_FCGI_BINARY:-'/usr/bin/spawn-fcgi'}

export FCGIWRAP_BINARY=${FCGIWRAP_BINARY:-'/usr/bin/fcgiwrap'}
export FCGIWRAP_PID=${FCGIWRAP_PID:-'/var/run/fcgiwrap.pid'}
export FCGIWRAP_SOCKET=${FCGIWRAP_SOCKET:-'/var/run/fcgiwrap.sock'}

export GIT_HTTP_BACKEND=${GIT_HTTP_BACKEND:-'/usr/libexec/git-core/git-http-backend'}

# install
apk add \
  spawn-fcgi fcgiwrap git-daemon

FCGIWRAP_DAEMON='/usr/sbin/fcgiwrap'

cat "${SETUP_PATH}/files/fcgiwrap" | \
  envsubst '${GITKIT_USER} ${GITKIT_GROUP} ${SPAWN_FCGI_BINARY} ${FCGIWRAP_BINARY} ${FCGIWRAP_PID} ${FCGIWRAP_SOCKET}' \
  > "${FCGIWRAP_DAEMON}"

chmod ugo+x "${FCGIWRAP_DAEMON}"

# cleanup
deluser --remove-home 'fcgiwrap'
rm -rf '/run/fcgiwrap'
