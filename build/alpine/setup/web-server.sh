#!/usr/bin/env ash

# global config variables
export NGINX_PID=${NGINX_PID:-'/var/run/nginx.pid'}
export NGINX_CONFIG=${NGINX_CONFIG:-'/etc/nginx/nginx.conf'}
export NGINX_SITE_CONFIG=${NGINX_SITE_CONFIG:-'/etc/nginx/conf.d/default.conf'}

# install php and modules
apk add \
  nginx

# update config files
cat "${SETUP_PATH}/files/nginx.augeas" | \
  envsubst '${GITKIT_USER} ${GITKIT_GROUP} ${NGINX_CONFIG} ${NGINX_PID}' | \
  augtool --noload --transform="Nginx incl ${NGINX_CONFIG}"

# create files and directories
mkdir '/tmp/nginx'
chown -R "${GITKIT_USER}:${GITKIT_GROUP}" '/tmp/nginx'
chown -R "${GITKIT_USER}:${GITKIT_GROUP}" '/var/lib/nginx'
chown -R "${GITKIT_USER}:${GITKIT_GROUP}" '/var/tmp/nginx'

# cleanup
deluser 'nginx'
