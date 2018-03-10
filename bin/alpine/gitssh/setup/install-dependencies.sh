#!/usr/bin/env ash

# Install software
apk update
## tools needed for setup (composer will be installed later)
apk add \
  augeas
## server and utilities
apk add \
  file gettext shadow git openssh-server

apk add mc

# Create host SSH keys
## rsa 4096 bits, no comment, no passphrase
ssh-keygen -t rsa -b 4096 -C '' -P '' -f '/etc/ssh/ssh_host_rsa_key'
## dsa 1024 bits, no comment, no passphrase
ssh-keygen -t dsa -b 1024 -C '' -P '' -f '/etc/ssh/ssh_host_dsa_key'
## ecdsa, no comment, no passphrase
ssh-keygen -t ecdsa -C '' -P '' -f '/etc/ssh/ssh_host_ecdsa_key'
## ed25519, no comment, no passphrase
ssh-keygen -t ed25519 -C '' -P '' -f '/etc/ssh/ssh_host_ed25519_key'
