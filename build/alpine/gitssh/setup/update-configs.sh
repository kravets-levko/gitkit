#!/usr/bin/env ash

# Update config files
cat "${SETUP_PATH}/files/sshd.augeas" | \
  envsubst "${_ENVSUBST_WHITELIST}" | \
  augtool --noload --transform="Sshd incl ${SSHD_CONFIG}"
