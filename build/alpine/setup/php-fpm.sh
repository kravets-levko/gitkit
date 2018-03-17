#!/usr/bin/env ash

# global config variables
export PHPFPM_SOCKET=${PHPFPM_SOCKET:-'/var/run/php-fpm7.sock'}
export PHPFPM_CONFIG=${PHPFPM_CONFIG:-'/etc/php7/php-fpm.d/www.conf'}

# install php and modules
apk add \
  php7-fpm php7-calendar php7-ctype php7-dom php7-exif php7-fileinfo php7-gd php7-gettext \
  php7-iconv php7-json php7-mbstring php7-mcrypt php7-opcache php7-openssl php7-posix \
  php7-simplexml php7-sockets php7-xml php7-xmlreader php7-xmlwriter php7-xsl php7-zlib

# update config files
cat "${SETUP_PATH}/files/php-fpm.augeas" | \
  envsubst '${GITKIT_USER} ${GITKIT_GROUP} ${PHPFPM_CONFIG} ${PHPFPM_SOCKET}' | \
  augtool --noload --transform="PHP incl ${PHPFPM_CONFIG}"
