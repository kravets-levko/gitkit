#!/usr/bin/env ash

# Important! Don't run this script as root!

HOME=$(eval echo ~$(id -un))

## php dependencies
echo "Installing dependencies..."
cd "${APPLICATION_PATH}"
composer install --no-scripts --no-plugins
rm -f "${APPLICATION_PATH}/composer.json"
rm -f "${APPLICATION_PATH}/composer.lock"

## themes
cd "${APPLICATION_PATH}/src/themes"
for THEME_PATH in $(find "$(pwd)" -mindepth 1 -maxdepth 1 -type d); do
  echo "Building '$(basename "${THEME_PATH}")' theme..."
  cd "${THEME_PATH}"
  npm install
  NODE_ENV=production npm run build
  rm -f "${THEME_PATH}/package-lock.json"
  rm -rf "${THEME_PATH}/node_modules"
done
