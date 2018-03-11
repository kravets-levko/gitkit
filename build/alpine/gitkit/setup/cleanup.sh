#!/usr/bin/env ash

# Remove unnecessary dependencies
apk del --purge \
  augeas nodejs php7 php7-phar

rm -f /usr/bin/composer

# Cleanup for application user
## remove home directory
rm -rf "$(eval echo ~${GITKIT_USER})"
usermod --home "" "${GITKIT_USER}"
