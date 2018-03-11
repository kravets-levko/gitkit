FROM alpine:latest

ARG SETUP_PATH="/tmp/gitssh/setup"

COPY ./build/alpine/docker/setup "${SETUP_PATH}/"
COPY ./build/alpine/gitssh/setup "${SETUP_PATH}/"
COPY ./build/alpine/gitssh/config.sh "${SETUP_PATH}/"
COPY ./build/alpine/gitssh/docker-start.sh "/"

RUN \
  . "${SETUP_PATH}/config.sh"; \
  export DOCKER_APP_USER="${GIT_USER}"; \
  export DOCKER_APP_GROUP="${GIT_GROUP}"; \
  # GIT user does not exist at this moment, therefore hard-code path
  export DOCKER_DATA_PATH="/home/${GIT_USER}"; \
  # Additional env
  export DOCKER_ENTRYPOINT_ENV="export SSHD_SERVER_KEYS='${SSHD_SERVER_KEYS}'"; \
  . "${SETUP_PATH}/setup.sh"; \
  . "${SETUP_PATH}/create-entrypoint.sh"; \
  rm -rf "${SETUP_PATH}";

EXPOSE 22

ENTRYPOINT ["/docker-entrypoint"]

CMD "/docker-start.sh"
