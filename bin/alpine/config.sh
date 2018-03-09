#!/usr/bin/env ash

export BINARIES_PATH=${BINARIES_PATH:-'/gitkit/bin'}
export APPLICATION_PATH=${APPLICATION_PATH:-'/gitkit/app'}

export WWW_USER=${WWW_USER:-'www-data'}
export WWW_GROUP=${WWW_GROUP:-'www-data'}

export GIT_USER=${GIT_USER:-'git'}
export GIT_GROUP=${GIT_GROUP:-'git'}
export GIT_BINARY=${GIT_BINARY:-'/usr/bin/git'}
export GIT_HTTP_BACKEND=${GIT_HTTP_BACKEND:-'/usr/libexec/git-core/git-http-backend'}
export GIT_REPOSITORIES_PATH=${GIT_REPOSITORIES_PATH:-'/var/lib/gitkit/repositories'}

export SSH_KEYGEN_BINARY=${SSH_KEYGEN_BINARY:-'/usr/bin/ssh-keygen'}
export SSH_AUTHORISED_KEYS=${SSH_AUTHORISED_KEYS:-'/var/lib/gitkit/authorized_keys'}

export FCGIWRAP_PID=${FCGIWRAP_PID:-'/var/run/fcgiwrap.pid'}
export FCGIWRAP_SOCKET=${FCGIWRAP_SOCKET:-'/var/run/fcgiwrap.sock'}

export PHPFPM_CONFIG=${PHPFPM_CONFIG:-'/etc/php7/php-fpm.d/www.conf'}
export PHPFPM_SOCKET=${PHPFPM_SOCKET:-'/var/run/php-fpm7.sock'}

export NGINX_PID=${NGINX_PID:-'/var/run/nginx.pid'}
export NGINX_CONFIG=${NGINX_CONFIG:-'/etc/nginx/nginx.conf'}
export NGINX_SITE_CONFIG=${NGINX_SITE_CONFIG:-'/etc/nginx/conf.d/default.conf'}
