#!/usr/bin/env ash

export GIT_USER=${GIT_USER:-'git'}
export GIT_GROUP=${GIT_GROUP:-'git'}

export SSHD_CONFIG=${SSHD_CONFIG:-'/etc/ssh/sshd_config'}
export SSHD_SERVER_KEYS=${SSHD_SERVER_KEYS:-'/etc/ssh/host_keys'}
export SSHD_AUTHORISED_KEYS=${SSHD_AUTHORISED_KEYS:-'/etc/ssh/authorized_keys'}
export SSHD_PID=${SSHD_PID:-'/var/run/sshd.pid'}

export USERADD_CONFIG=${USERADD_CONFIG:-'/etc/default/useradd'}
