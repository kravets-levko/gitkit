#!/usr/bin/env ash

# `fcgiwrap` user created during installation of `fcgiwrap`. it should be removed
# because it's primary group is `www-data` and we're going to modify it
deluser --remove-home 'fcgiwrap' &>/dev/null

# Add WWW user
adduser -H -D -s '/sbin/nologin' -G "${WWW_GROUP}" "${WWW_USER}"

# Add user that will own application sources
adduser -D -s '/sbin/nologin' -u 7007 -G "${WWW_GROUP}" "${GITKIT_USER}"

# Change owner of application sources
chown -R "${GITKIT_USER}:${WWW_GROUP}" "${APPLICATION_PATH}"
chmod -R ugo+r "${APPLICATION_PATH}"
