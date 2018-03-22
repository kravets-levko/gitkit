#!/usr/bin/env ash

function start_php() {
  php-fpm7 -F -O
}

function start_git() {
  fcgiwrap
}

function start_sshd() {
  # 1. find ssh private keys (files without `.pub` extension) that have corresponding file with `.pub` extension
  #    print NULL-delimited names (for proper whitespace handling)
  # 2. extend each args (filename) with `-h` - also use NULL as delimiter
  # 3. execute `sshd` - it requires absolute path; path all new arguments to it
  find "${SSHD_SERVER_KEYS}" -type f -and -not -path '*.pub' -and -exec test -f '{}.pub' \; -print0 2>'/dev/null' | \
    xargs -0 -r -- printf "-h\0%s\0" | \
    xargs -0 -r -- '/usr/sbin/sshd' -D -e
}

function start_web() {
  nginx -g 'daemon off; error_log /dev/stdout info;'
}

start_git & start_php & start_web & start_sshd
