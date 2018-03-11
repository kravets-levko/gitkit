#!/usr/bin/env ash

# Add GIT user

## assign gid
addgroup -g 7007 "${GIT_GROUP}"

## with password, restricted shell, with home directory, assign uid and group
useradd --create-home --home-dir "/home/${GIT_USER}" --skel /dev/null \
  --shell '/usr/bin/git-shell' --password "${GIT_USER}" \
  --gid "${GIT_GROUP}" --uid 7007 "${GIT_USER}"

## create additional files and directories for GIT user

_GIT_HOME="$(eval echo ~"${GIT_USER}")"

mkdir "${_GIT_HOME}/git-shell-commands"
chown -R "${GIT_USER}:${GIT_GROUP}" "${_GIT_HOME}"
