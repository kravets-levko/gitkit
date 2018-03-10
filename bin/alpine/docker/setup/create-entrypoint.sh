#!/usr/bin/env ash

set -e

_ENTRYPOINT='/docker-entrypoint'

cat "${SETUP_PATH}/files/docker-entrypoint.sh" |
  envsubst "${_ENVSUBST_WHITELIST}" > "${_ENTRYPOINT}"

chmod ugo+rx "${_ENTRYPOINT}";
