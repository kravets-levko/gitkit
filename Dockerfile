FROM alpine:latest

ENV \
  BINARIES_PATH="/gitkit/bin" \
  APPLICATION_PATH="/gitkit/app"

COPY ./bin/alpine/setup/gitkit "${BINARIES_PATH}"
COPY ./bin/alpine/config.sh "${BINARIES_PATH}/"
COPY ./bin/alpine/docker-entrypoint.sh "${BINARIES_PATH}/"
COPY ./gitkit "${APPLICATION_PATH}"

RUN \
  . "${BINARIES_PATH}/config.sh"; \
  . "${BINARIES_PATH}/setup.sh";

# Create entrypoint script:
# - common config file (`config.sh`)
# - additional config variables needed for `docker-entrypoint.sh`
# - `docker-entrypoint.sh` itself
RUN \
  _ENTRYPOINT='/docker-entrypoint'; \
  cat "${BINARIES_PATH}/config.sh" >> "${_ENTRYPOINT}"; \
  echo 'export DOCKER_APP_USER="${WWW_USER}"' >> "${_ENTRYPOINT}"; \
  echo 'export DOCKER_APP_GROUP="${WWW_GROUP}"' >> "${_ENTRYPOINT}"; \
  cat "${BINARIES_PATH}/docker-entrypoint.sh" >> "${_ENTRYPOINT}"; \
  chmod ugo+rx "${_ENTRYPOINT}"; \
  rm -rf "${BINARIES_PATH}"

COPY ./bin/alpine/docker-start-gitkit.sh "${BINARIES_PATH}/"

EXPOSE 80

ENTRYPOINT ["/docker-entrypoint"]

CMD "${BINARIES_PATH}/docker-start-gitkit.sh"
