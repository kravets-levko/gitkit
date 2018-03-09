#!/usr/bin/env ash

php-fpm7 && fcgiwrap && nginx -g 'daemon off; error_log /dev/stdout info;'
