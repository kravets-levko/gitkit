#!/usr/bin/env ash

set -e

apk update

. "${SETUP_PATH}/prepare.sh"
. "${SETUP_PATH}/php-fpm.sh"
. "${SETUP_PATH}/git-http-backend.sh"
. "${SETUP_PATH}/web-server.sh"
. "${SETUP_PATH}/ssh-server.sh"
. "${SETUP_PATH}/application.sh"
. "${SETUP_PATH}/cleanup.sh"
