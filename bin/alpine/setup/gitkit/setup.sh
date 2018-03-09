#!/usr/bin/env ash

set -e

export GITKIT_USER=${GITKIT_USER:-'gitkit'}

# Capture all variables available at this moment
export _ENVSUBST_WHITELIST=$(env | sed -e 's/^\([^=]\{1,\}\)=.*/$\1/g' | tr '\n\r' ' ')

. "${BINARIES_PATH}/install-dependencies.sh"
. "${BINARIES_PATH}/setup-users.sh"
. "${BINARIES_PATH}/update-configs.sh"

# This script shoukd be run under non-root user with home directory
su --shell /bin/ash --login ${GITKIT_USER} --preserve-environment \
  --command "${BINARIES_PATH}/build-application.sh"

. "${BINARIES_PATH}/setup-folders.sh"

. "${BINARIES_PATH}/cleanup.sh"
