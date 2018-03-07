FROM alpine:latest

# Install software
RUN apk update && apk add \
  ## tools needed for setup (composer will be installed later)
  augeas \
  nodejs \
  php7 \
  php7-phar \
  ## server and utilities
  file \
  git \
  nginx \
  openssh-keygen \
  shadow \
  ## php and modules
  php7-fpm \
  php7-calendar \
  php7-ctype \
  php7-dom \
  php7-exif \
  php7-fileinfo \
  php7-gd \
  php7-gettext \
  php7-iconv \
  php7-json \
  php7-mbstring \
  php7-mcrypt \
  php7-opcache \
  php7-openssl \
  php7-posix \
  php7-simplexml \
  php7-sockets \
  php7-xml \
  php7-xmlreader \
  php7-xmlwriter \
  php7-xsl \
  php7-zlib \
  ## git access via http
  fcgiwrap \
  git-daemon \
  spawn-fcgi \
;

# Install composer
RUN \
  php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"; \
  php -r "if (hash_file('SHA384', 'composer-setup.php') === '544e09ee996cdf60ece3804abc52599c22b1f40f4323403c44d44fdfdd586475ca9813a858088ffbc1f233e9b180f061') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"; \
  php composer-setup.php --install-dir=bin --filename=composer; \
  php -r "unlink('composer-setup.php');";

# Fix files, directories, configs etc.
ENV \
  WWW_USER="www-data" \
  WWW_GROUP="www-data" \
  APPLICATION_DATA="/var/lib/gitkit" \
  NGINX_CONF="/etc/nginx/nginx.conf" \
  NGINX_DEFAULT_CONF="/etc/nginx/conf.d/default.conf" \
  APP_NGINX_CONF="/app/nginx.conf" \
  PHP_FPM_CONF="/etc/php7/php-fpm.d/www.conf" \
  PHP_FPM_SOCKET="/var/run/php-fpm7.sock"

RUN \
  ## this user was created during `fcgiwrap` installation but it's not needed
  (deluser --remove-home fcgiwrap &>/dev/null || true) && \
  ## create user and group (delete if aleady exists)
  (deluser --remove-home ${WWW_USER} &>/dev/null || true) && \
  (delgroup ${WWW_GROUP} &>/dev/null || true) && \
  addgroup ${WWW_GROUP} && \
  adduser -H -D -s /bin/false -G ${WWW_GROUP} ${WWW_USER} && \
  ## link application nginx config in place of default one
  rm "${NGINX_DEFAULT_CONF}" && \
  ln -s "${APP_NGINX_CONF}" "${NGINX_DEFAULT_CONF}" && \
  ## update nginx user/group and pid file path
  augtool --autosave --noload --transform="Nginx incl ${NGINX_CONF}" \
    set "/files${NGINX_CONF}/user" "${WWW_USER}" && \
  augtool --autosave --noload --transform="Nginx incl ${NGINX_CONF}" \
    set "/files${NGINX_CONF}/pid" "/var/run/nginx.pid" && \
  ## update php-fpm user/group
  augtool --autosave --noload --transform="PHP incl ${PHP_FPM_CONF}" \
    set "/files${PHP_FPM_CONF}/www/user" "${WWW_USER}" && \
  augtool --autosave --noload --transform="PHP incl ${PHP_FPM_CONF}" \
    set "/files${PHP_FPM_CONF}/www/group" "${WWW_GROUP}" && \
  ## php-fpm should use unix socket with the same user
  augtool --autosave --noload --transform="PHP incl ${PHP_FPM_CONF}" \
    set "/files${PHP_FPM_CONF}/www/listen" "${PHP_FPM_SOCKET}" && \
  augtool --autosave --noload --transform="PHP incl ${PHP_FPM_CONF}" \
    set "/files${PHP_FPM_CONF}/www/listen.owner" "${WWW_USER}" && \
  augtool --autosave --noload --transform="PHP incl ${PHP_FPM_CONF}" \
    set "/files${PHP_FPM_CONF}/www/listen.group" "${WWW_GROUP}" && \
  ## create application data directories
  mkdir "${APPLICATION_DATA}" && \
  mkdir "${APPLICATION_DATA}/.ssh" && \
  touch "${APPLICATION_DATA}/.ssh/authorized_keys" && \
  chmod -R g+rw "${APPLICATION_DATA}";

# Install application
COPY . /app
COPY ./bin/fcgiwrap /usr/sbin/fcgiwrap
## TODO: Install dependencies, build assets and cleanup

# Cleanup
RUN \
  rm -f /bin/composer && \
  apk del --purge \
    augeas \
    nodejs \
    php7 \
    php7-phar \
;

EXPOSE 80

ENTRYPOINT ["/app/docker/gitkit/entrypoint"]

CMD php-fpm7 && fcgiwrap && nginx -g 'daemon off; error_log /dev/stdout info;'
