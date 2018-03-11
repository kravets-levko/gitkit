#!/usr/bin/env ash

mkdir -p "${GIT_REPOSITORIES_PATH}" && \
  chown -R "${WWW_USER}:${WWW_GROUP}" "${GIT_REPOSITORIES_PATH}"

_SSH_AUTHORISED_KEYS_DIR="$(dirname ${SSH_AUTHORISED_KEYS})"

mkdir -p "${_SSH_AUTHORISED_KEYS_DIR}" && \
  chown -R "${WWW_USER}:${WWW_GROUP}" "${_SSH_AUTHORISED_KEYS_DIR}"

touch "${SSH_AUTHORISED_KEYS}" && \
  chown -R "${WWW_USER}:${WWW_GROUP}" "${SSH_AUTHORISED_KEYS}"
