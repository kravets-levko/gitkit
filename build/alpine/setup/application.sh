#!/usr/bin/env ash

# global config variables
export GIT_BINARY=${GIT_BINARY:-'/usr/bin/git'}
export GITKIT_ENV_FILE=${GITKIT_ENV_FILE:-"${GITKIT_DATA_PATH}/.env"}

# install git and tools to build application (php cli, composer, node.js)
apk add \
  git nodejs php7 php7-phar

php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php -r "if (hash_file('SHA384', 'composer-setup.php') === '544e09ee996cdf60ece3804abc52599c22b1f40f4323403c44d44fdfdd586475ca9813a858088ffbc1f233e9b180f061') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
php composer-setup.php --install-dir=/usr/bin --filename=composer
php -r "unlink('composer-setup.php');"

# update config files
cat "${SETUP_PATH}/files/nginx.conf" | \
  envsubst '
    ${GITKIT_USER} ${GITKIT_GROUP} ${GITKIT_ENV_FILE} ${APPLICATION_PATH} ${GIT_REPOSITORIES_PATH}
    ${GIT_BINARY} ${GIT_HTTP_BACKEND} ${SSH_KEYGEN_BINARY} ${SSHD_AUTHORISED_KEYS}
    ${PHPFPM_SOCKET} ${FCGIWRAP_SOCKET}
  ' \
  > "${NGINX_SITE_CONFIG}"

cat "${SETUP_PATH}/files/.env" | \
  envsubst '${GITKIT_USER} ${GITKIT_GROUP}' \
  > "${GITKIT_ENV_FILE}"

# prepare files and directories
chown -R "${GITKIT_USER}:${GITKIT_GROUP}" "${APPLICATION_PATH}"
chown -R "${GITKIT_USER}:${GITKIT_GROUP}" "${GITKIT_ENV_FILE}"

# build application
su --shell /bin/ash --login "${GITKIT_USER}" \
  --command "cd '${APPLICATION_PATH}' && composer install --no-scripts --no-plugins"
rm -f "${APPLICATION_PATH}/composer.json"
rm -f "${APPLICATION_PATH}/composer.lock"

for THEME_PATH in $(find "${APPLICATION_PATH}/src/themes" -mindepth 1 -maxdepth 1 -type d); do
  echo "Building '$(basename "${THEME_PATH}")' theme..."
  su --shell /bin/ash --login "${GITKIT_USER}" \
    --command "cd '${THEME_PATH}' && npm install"
  su --shell /bin/ash --login "${GITKIT_USER}" \
    --command "cd '${THEME_PATH}' && NODE_ENV=production npm run build"
  rm -f "${THEME_PATH}/package-lock.json"
  rm -rf "${THEME_PATH}/node_modules"
done

# cleanup

apk del \
  nodejs php7 php7-phar

rm -f '/usr/bin/composer'
rm -rf '/tmp/phantomjs'
