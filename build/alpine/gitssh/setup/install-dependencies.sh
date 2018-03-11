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

# `useradd`: do not create mail folders for new users
augtool --autosave --noload --transform="Shellvars incl ${USERADD_CONFIG}" \
 set "/files${USERADD_CONFIG}/CREATE_MAIL_SPOOL" 'no'
