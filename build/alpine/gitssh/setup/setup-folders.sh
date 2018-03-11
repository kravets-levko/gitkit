#!/usr/bin/env ash

# Create host SSH keys
mkdir "${SSHD_SERVER_KEYS}"
chmod ugo+r "${SSHD_SERVER_KEYS}"
## rsa 4096 bits, no comment, no passphrase
ssh-keygen -t rsa -b 4096 -C '' -P '' -f "${SSHD_SERVER_KEYS}/rsa"
## dsa 1024 bits, no comment, no passphrase
ssh-keygen -t dsa -b 1024 -C '' -P '' -f "${SSHD_SERVER_KEYS}/dsa"
## ecdsa, no comment, no passphrase
ssh-keygen -t ecdsa -C '' -P '' -f "${SSHD_SERVER_KEYS}/ecdsa"
## ed25519, no comment, no passphrase
ssh-keygen -t ed25519 -C '' -P '' -f "${SSHD_SERVER_KEYS}/ed25519"

# Create authorized keys file
mkdir -p "$(dirname ${SSHD_AUTHORISED_KEYS})" && \
  touch "${SSHD_AUTHORISED_KEYS}"
