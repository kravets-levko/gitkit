#!/usr/bin/env ash

set -e

export GITKIT_USER=${GITKIT_USER:-'gitkit'}

# Capture all variables available at this moment
export _ENVSUBST_WHITELIST=$(env | sed -e 's/^\([^=]\{1,\}\)=.*/$\1/g' | tr '\n\r' ' ')

. "${SETUP_PATH}/install-dependencies.sh"
. "${SETUP_PATH}/setup-users.sh"
. "${SETUP_PATH}/update-configs.sh"

# This script shoukd be run under non-root user with home directory
su --shell /bin/ash --login ${GITKIT_USER} --preserve-environment \
  --command "${SETUP_PATH}/build-application.sh"

. "${SETUP_PATH}/setup-folders.sh"

. "${SETUP_PATH}/cleanup.sh"
