#!/usr/bin/env ash

set -e

# Capture all variables available at this moment
export _ENVSUBST_WHITELIST=$(env | sed -e 's/^\([^=]\{1,\}\)=.*/$\1/g' | tr '\n\r' ' ')

. "${SETUP_PATH}/install-dependencies.sh"
. "${SETUP_PATH}/setup-users.sh"
. "${SETUP_PATH}/update-configs.sh"
. "${SETUP_PATH}/cleanup.sh"
