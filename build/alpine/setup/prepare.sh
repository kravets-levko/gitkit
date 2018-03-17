#!/usr/bin/env ash

apk add \
  augeas file gettext shadow

# `useradd`: do not create mail folders for new users
USERADD_CONFIG='/etc/default/useradd'

augtool --autosave --noload --transform="Shellvars incl ${USERADD_CONFIG}" \
 set "/files${USERADD_CONFIG}/CREATE_MAIL_SPOOL" 'no'

# add user and initialize directories and files

mkdir -p "${GIT_REPOSITORIES_PATH}"

## assign gid
addgroup -g 7007 "${GITKIT_GROUP}"

## with password, restricted shell, with home directory, assign uid and group
useradd --create-home --home-dir "${GIT_REPOSITORIES_PATH}" --skel /dev/null \
  --shell '/usr/bin/git-shell' --password "${GITKIT_USER}" \
  --gid "${GITKIT_GROUP}" --uid 7007 "${GITKIT_USER}"

chown -R "${GITKIT_USER}:${GITKIT_GROUP}" "${GIT_REPOSITORIES_PATH}"
