#!/usr/bin/env ash

# Important! Don't run this script as root!

HOME=$(eval echo ~$(id -un))

## php dependencies
echo "Installing dependencies..."
cd "${APPLICATION_PATH}"
composer install --no-scripts --no-plugins

## build themes
THEMES_PATH="${APPLICATION_PATH}/src/themes"
PUBLIC_PATH="${APPLICATION_PATH}/public"

## Clear public directory
rm -rf "${PUBLIC_PATH}"

## Build each theme and copy files to /public
for THEME_PATH in $(ls -d ${THEMES_PATH}/*/); do
  THEME_PATH=$(realpath "${THEME_PATH}")
  THEME_NAME=$(basename "${THEME_PATH}")

  echo "Building '${THEME_NAME}' theme..."

  rm -rf "${THEME_PATH}/node_modules"
  SOURCE_PATH="${THEME_PATH}/public"
  TARGET_PATH="${PUBLIC_PATH}/themes/${THEME_NAME}"

  cd "${THEME_PATH}"
  npm install
  BUNDLE_OPTIMISE=yes npm run build
  mkdir -p "${TARGET_PATH}"
  cp -R "${SOURCE_PATH}/." "${TARGET_PATH}"

  rm -rf "${SOURCE_PATH}"
  rm -rf "${THEME_PATH}/node_modules"
done
