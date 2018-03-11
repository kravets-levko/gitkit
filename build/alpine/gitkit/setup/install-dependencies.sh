#!/usr/bin/env ash

# Install software
apk update
## tools needed for setup (composer will be installed later)
apk add \
  augeas nodejs php7 php7-phar
## server and utilities
apk add \
  file gettext shadow git nginx openssh-keygen
## php and modules
apk add \
  php7-fpm php7-calendar php7-ctype php7-dom php7-exif php7-fileinfo php7-gd php7-gettext \
  php7-iconv php7-json php7-mbstring php7-mcrypt php7-opcache php7-openssl php7-posix \
  php7-simplexml php7-sockets php7-xml php7-xmlreader php7-xmlwriter php7-xsl php7-zlib
## git access via http
apk add \
  fcgiwrap git-daemon spawn-fcgi

# `useradd`: do not create mail folders for new users
augtool --autosave --noload --transform="Shellvars incl ${USERADD_CONFIG}" \
 set "/files${USERADD_CONFIG}/CREATE_MAIL_SPOOL" 'no'

# Install fcgiwrap daemon
cat "${SETUP_PATH}/files/fcgiwrap" | \
  envsubst "${_ENVSUBST_WHITELIST}" > "/usr/sbin/fcgiwrap"
chmod ugo+x "/usr/sbin/fcgiwrap"

# Install composer
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php -r "if (hash_file('SHA384', 'composer-setup.php') === '544e09ee996cdf60ece3804abc52599c22b1f40f4323403c44d44fdfdd586475ca9813a858088ffbc1f233e9b180f061') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
php composer-setup.php --install-dir=/usr/bin --filename=composer
php -r "unlink('composer-setup.php');"
