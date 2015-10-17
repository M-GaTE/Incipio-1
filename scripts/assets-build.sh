#!/usr/bin/env bash
#
# Build steps for the Symfony application.

assetsBeforeInstall() {
  isAssetsBuild
  if [[ 1 = "$?" ]]; then
    log "Skipped"

    return 0
  fi

  log "'gem: --no-ri --no-rdoc' > .gemrc ~/."
  echo 'gem: --no-ri --no-rdoc' > .gemrc ~/.

  log "nvm install 0.12"
  nvm install 0.12

  log "npm install --global npm"
  npm install --global npm

  log "npm install --global gulp"
  npm install --global gulp

  log "composer global update --prefer-dist"
  composer global update --prefer-dist

  log "composer install --prefer-dist"
  composer install --prefer-dist
}

assetsInstall() {
  isAssetsBuild
  if [[ 1 = "$?" ]]; then
    log "Skipped"

    return 0
  fi

  log "gem install csscss"
  gem install csscss

  log "gem install scss_lint"
  gem install scss_lint

  log "gem update"
  gem update

  log "npm install"
  npm install
}

assetsScript() {
  isAssetsBuild
  if [[ 1 = "$?" ]]; then
    log "Skipped"

    return 0
  fi

  log "gulp build"
  gulp build

  log "gulp check:css"
  gulp check:css

  log "gulp lint:css"
  gulp lint:css

  log "gulp lint:js"
  gulp lint:js

  log "php app/console lint:yaml behat.yml"
  php app/console lint:yaml behat.yml

  log "php app/console lint:yaml app"
  php app/console lint:yaml app

  log "php app/console lint:yaml src"
  php app/console lint:yaml src
}

export -f assetsBeforeInstall
export -f assetsInstall
export -f assetsScript
