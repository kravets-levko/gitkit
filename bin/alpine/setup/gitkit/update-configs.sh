#!/usr/bin/env ash

# Update config files
cat "${SETUP_PATH}/files/gitkit.nginx.augeas" | \
  envsubst "${_ENVSUBST_WHITELIST}" | \
  augtool --noload --transform="Nginx incl ${NGINX_CONFIG}"

cat "${SETUP_PATH}/files/gitkit.php-fpm.augeas" | \
  envsubst "${_ENVSUBST_WHITELIST}" | \
  augtool --noload --transform="PHP incl ${PHPFPM_CONFIG}"

cat "${SETUP_PATH}/files/gitkit.nginx.conf" | \
  envsubst "${_ENVSUBST_WHITELIST}" > "${NGINX_SITE_CONFIG}"
