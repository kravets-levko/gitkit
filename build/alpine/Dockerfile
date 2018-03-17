FROM alpine:latest

ARG SETUP_PATH="/tmp/gitkit"
ARG APPLICATION_PATH="/app"

COPY "./build/alpine/setup" "${SETUP_PATH}/"
COPY "./build/alpine/config.sh" "${SETUP_PATH}/"
COPY "./build/alpine/docker-start.sh" "/"
COPY "./app" "${APPLICATION_PATH}"

RUN \
  # setup application
  . "${SETUP_PATH}/config.sh"; \
  . "${SETUP_PATH}/setup.sh"; \
  # create docker entrypoint
  . "${SETUP_PATH}/docker-entrypoint.sh"; \
  # cleanup
  rm -rf "${SETUP_PATH}";

EXPOSE 80

ENTRYPOINT ["/docker-entrypoint"]

CMD "/docker-start.sh"
