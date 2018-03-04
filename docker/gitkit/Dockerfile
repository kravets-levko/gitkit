FROM ubuntu:xenial

# Install utilities
RUN apt-get update
RUN apt-get install --yes apt-utils apt-transport-https lsb-release curl software-properties-common mc

# Add additional repositories

## nginx
RUN curl -s https://nginx.org/keys/nginx_signing.key | apt-key add -
RUN echo "deb http://nginx.org/packages/ubuntu/ $(lsb_release -c -s) nginx" | cat >> /etc/apt/sources.list.d/nginx.list
RUN echo "deb-src http://nginx.org/packages/ubuntu/ $(lsb_release -c -s) nginx" | cat >> /etc/apt/sources.list.d/nginx.list

## php 7.2
RUN LC_ALL=C.UTF-8 add-apt-repository ppa:ondrej/php

## node.js 8.x
RUN curl -s https://deb.nodesource.com/gpgkey/nodesource.gpg.key | apt-key add -
RUN echo "deb https://deb.nodesource.com/node_8.x/ $(lsb_release -c -s) main" | cat >> /etc/apt/sources.list.d/nodejs.list
RUN echo "deb-src https://deb.nodesource.com/node_8.x/ $(lsb_release -c -s) main" | cat >> /etc/apt/sources.list.d/nodejs.list

RUN apt-get update

# Install additional software
RUN apt-get install --yes nginx git php7.2-fpm nodejs fcgiwrap

## Composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php -r "if (hash_file('SHA384', 'composer-setup.php') === '544e09ee996cdf60ece3804abc52599c22b1f40f4323403c44d44fdfdd586475ca9813a858088ffbc1f233e9b180f061') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
RUN php composer-setup.php --install-dir=bin --filename=composer
RUN php -r "unlink('composer-setup.php');"

## Check installed software
RUN nginx -v && php -v && composer --version && git --version && node -v

# Update nginx config
RUN rm /etc/nginx/conf.d/default.conf
RUN ln -s /app/nginx.conf /etc/nginx/conf.d/default.conf
RUN sed -i -e 's/user\s\+[-_a-zA-Z0-9]\+;/user www-data;/g' /etc/nginx/nginx.conf

# Create necessary files and directories
RUN mkdir /home/git
RUN cd /home/git
RUN mkdir .ssh
RUN touch .ssh/authorized_keys
RUN chmod -R g+rw /home/git

# Install application
ADD . /app

EXPOSE 80

ENTRYPOINT ["/app/docker/gitkit/entrypoint"]

CMD service php7.2-fpm start && service fcgiwrap start && \
  service php7.2-fpm status && service fcgiwrap status && \
  nginx -g 'daemon off; error_log /dev/stdout info;'
