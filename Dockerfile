FROM alpine:latest

ARG SETUP_PATH="/tmp/gitkit/setup"
ARG APPLICATION_PATH="/app"

COPY ./bin/alpine/setup/gitkit "${SETUP_PATH}"
COPY ./bin/alpine/config.sh "${SETUP_PATH}/"
COPY ./bin/alpine/docker-entrypoint.sh "${SETUP_PATH}/"
COPY ./app "${APPLICATION_PATH}"

RUN \
  . "${SETUP_PATH}/config.sh"; \
  export DOCKER_APP_USER="${WWW_USER}"; \
  export DOCKER_APP_GROUP="${WWW_GROUP}"; \
  . "${SETUP_PATH}/setup.sh"; \
  # create entrypoint script (all variables from setup script are still available)
  set -x; \
  _ENTRYPOINT='/docker-entrypoint'; \
  cat "${SETUP_PATH}/docker-entrypoint.sh" | envsubst "${_ENVSUBST_WHITELIST}" > "${_ENTRYPOINT}"; \
  chmod ugo+rx "${_ENTRYPOINT}"; \
  rm -rf "${SETUP_PATH}";

EXPOSE 80

ENTRYPOINT ["/docker-entrypoint"]

CMD php-fpm7 && fcgiwrap && nginx -g 'daemon off; error_log /dev/stdout info;'
