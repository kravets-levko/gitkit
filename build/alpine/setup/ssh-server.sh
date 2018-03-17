#!/usr/bin/env ash

# global config variables
export SSHD_CONFIG=${SSHD_CONFIG:-'/etc/ssh/sshd_config'}
export SSHD_PID=${SSHD_PID:-'/var/run/sshd.pid'}
export SSHD_SERVER_KEYS=${SSHD_SERVER_KEYS:-"${GITKIT_DATA_PATH}/server_keys"}
export SSHD_AUTHORISED_KEYS=${SSHD_AUTHORISED_KEYS:-"${GITKIT_DATA_PATH}/authorized_keys"}

export SSH_KEYGEN_BINARY=${SSH_KEYGEN_BINARY:-'/usr/bin/ssh-keygen'}

# install
apk add \
  openssh-server

# update config files
cat "${SETUP_PATH}/files/sshd.augeas" | \
  envsubst '${SSHD_CONFIG} ${SSHD_PID} ${SSHD_AUTHORISED_KEYS} ${SSHD_SERVER_KEYS}' | \
  augtool --noload --transform="Sshd incl ${SSHD_CONFIG}"

# create directories and files

## create host SSH keys
mkdir "${SSHD_SERVER_KEYS}"
chmod ugo+r "${SSHD_SERVER_KEYS}"
ssh-keygen -t rsa     -b 4096 -C '' -P '' -f "${SSHD_SERVER_KEYS}/rsa"
ssh-keygen -t dsa     -b 1024 -C '' -P '' -f "${SSHD_SERVER_KEYS}/dsa"
ssh-keygen -t ecdsa           -C '' -P '' -f "${SSHD_SERVER_KEYS}/ecdsa"
ssh-keygen -t ed25519         -C '' -P '' -f "${SSHD_SERVER_KEYS}/ed25519"

## create authorized keys file
mkdir -p "$(dirname ${SSHD_AUTHORISED_KEYS})" && \
  touch "${SSHD_AUTHORISED_KEYS}"
chown -R "${GITKIT_USER}:${GITKIT_GROUP}" "${SSHD_AUTHORISED_KEYS}"
