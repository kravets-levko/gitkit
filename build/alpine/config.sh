#!/usr/bin/env ash

export GITKIT_DATA_PATH=${GITKIT_DATA_PATH:-'/var/lib/gitkit'}

export GITKIT_USER=${GITKIT_USER:-'git'}
export GITKIT_GROUP=${GITKIT_GROUP:-'git'}

export GIT_REPOSITORIES_PATH=${GIT_REPOSITORIES_PATH:-"${GITKIT_DATA_PATH}/repositories"}
