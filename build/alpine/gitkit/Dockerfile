FROM alpine:latest

ARG SETUP_PATH="/tmp/gitkit/setup"
ARG APPLICATION_PATH="/app"

COPY ./build/alpine/docker/setup "${SETUP_PATH}/"
COPY ./build/alpine/gitkit/setup "${SETUP_PATH}/"
COPY ./build/alpine/gitkit/config.sh "${SETUP_PATH}/"
COPY ./build/alpine/gitkit/docker-start.sh "/"
COPY ./app "${APPLICATION_PATH}"

RUN \
  . "${SETUP_PATH}/config.sh"; \
  export DOCKER_APP_USER="${WWW_USER}"; \
  export DOCKER_APP_GROUP="${WWW_GROUP}"; \
  export DOCKER_DATA_PATH="${GIT_REPOSITORIES_PATH}"; \
  . "${SETUP_PATH}/setup.sh"; \
  . "${SETUP_PATH}/create-entrypoint.sh"; \
  rm -rf "${SETUP_PATH}";

EXPOSE 80

ENTRYPOINT ["/docker-entrypoint"]

CMD "/docker-start.sh"
