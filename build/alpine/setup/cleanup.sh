#!/usr/bin/env ash

# remove unnecessary software
apk del \
  augeas

# clean repositories path
find "${GIT_REPOSITORIES_PATH}" -mindepth 1 -maxdepth 1 -print0 | xargs -0 -r -n 1 -- rm -rf;
