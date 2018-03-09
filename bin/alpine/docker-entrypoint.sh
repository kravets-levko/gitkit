#!/usr/bin/env ash

echo "Mapping user from host machine"

HOST_UID=$(stat -c "%u" "${GIT_REPOSITORIES_PATH}")
HOST_GID=$(stat -c "%g" "${GIT_REPOSITORIES_PATH}")

if [ "${HOST_UID}" == "0" ]; then
  echo "Application files cannot be owned by root"
  exit 1
fi
if [ "${HOST_GID}" == "0" ]; then
  echo "Application files cannot be owned by root"
  echo 1
fi

echo "Using ${HOST_UID}:${HOST_GID}"

usermod -u ${HOST_UID} ${DOCKER_APP_USER}
groupmod -g ${HOST_GID} ${DOCKER_APP_GROUP}

exec "$@"
