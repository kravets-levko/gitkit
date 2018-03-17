#!/usr/bin/env ash

PINK='\e[38;5;205m'
RED='\e[38;5;9m'
BLUE='\e[38;5;45m'
YELLOW='\e[38;5;221m'
GREEN='\e[38;5;114m'
VIOLET='\e[38;5;147m'
ORANGE='\e[38;5;214m'

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
  #echo -n > /var/lib/nginx/logs/error.log
  #echo -n > /var/lib/nginx/logs/access.log

  nginx -g 'daemon off; error_log /dev/stdout info;'
  #nginx -g 'daemon off;'
}

function log() {
  sed -e "s/^.\{1,\}\$/$(printf "$2")$1 &$(printf '\e[0m')/g"
}

start_git  2>&1 | log '[git]'   "${ORANGE}" & \
start_php  2>&1 | log '[php]'   "${VIOLET}" & \
start_web  2>&1 | log '[nginx]' "${BLUE}" & \
start_sshd 2>&1 | log '[sshd]'  "${GREEN}"
