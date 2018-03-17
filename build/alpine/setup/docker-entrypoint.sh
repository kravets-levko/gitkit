#!/usr/bin/env ash

set -e

_ENTRYPOINT_FILENAME='/docker-entrypoint'

cat "${SETUP_PATH}/files/docker-entrypoint.sh" |
  envsubst '${GITKIT_USER} ${GITKIT_GROUP} ${GIT_REPOSITORIES_PATH} ${SSHD_SERVER_KEYS}' \
  > "${_ENTRYPOINT_FILENAME}"

chmod ugo+rx "${_ENTRYPOINT_FILENAME}";
