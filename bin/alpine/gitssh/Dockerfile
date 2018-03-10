FROM alpine:latest

ARG SETUP_PATH="/tmp/gitssh/setup"

COPY ./bin/alpine/docker/setup "${SETUP_PATH}/"
COPY ./bin/alpine/gitssh/setup "${SETUP_PATH}/"
COPY ./bin/alpine/gitssh/config.sh "${SETUP_PATH}/"
COPY ./app "${APPLICATION_PATH}"

RUN \
  . "${SETUP_PATH}/config.sh"; \
  export DOCKER_APP_USER="${GIT_USER}"; \
  export DOCKER_APP_GROUP="${GIT_GROUP}"; \
  # GIT user does not exist at this moment, therefore hard-code path
  export DOCKER_DATA_PATH="/home/${GIT_USER}"; \
  . "${SETUP_PATH}/setup.sh"; \
  . "${SETUP_PATH}/create-entrypoint.sh"; \
  rm -rf "${SETUP_PATH}";

EXPOSE 22

ENTRYPOINT ["/docker-entrypoint"]

CMD /usr/sbin/sshd -D -e
