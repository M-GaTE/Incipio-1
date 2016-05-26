#!/usr/bin/env bash
#
# Build steps for the Symfony application.

appBeforeInstall() {
  isAppBuild
  if [[ 1 = "$?" ]]; then
    log "Skipped"

    return 0
  fi

  log "pip install --upgrade pip --user"
  pip install --upgrade pip --user

  log "composer self-update"
  composer self-update

  log "composer config -g github-oauth.github.com $GITHUB_OAUTH_TOKEN"
  composer config -g github-oauth.github.com $GITHUB_OAUTH_TOKEN

  log "composer global require phpunit/phpunit --no-update"
  composer global require phpunit/phpunit --no-update

  log "cp phpunit_travis.xml phpunit.xml"
  cp phpunit_travis.xml phpunit.xml

  log "setupCoverage"
  setupCoverage

  log "setupDeployment"
  setupDeployment
}

appInstall() {
  isAppBuild
  if [[ 1 = "$?" ]]; then
    log "Skipped"

    return 0
  fi

  log "composer global update --prefer-dist"
  composer global update --prefer-dist

  log "composer install --prefer-dist"
  composer install --prefer-dist

  log "php app/console doctrine:database:create --env=test"
  php app/console doctrine:database:create --env=test

  log "php app/console doctrine:schema:create --env=test"
  php app/console doctrine:schema:create --env=test
}

appScript() {
  isAppBuild
  if [[ 1 = "$?" ]]; then
    log "Skipped"

    return 0
  fi

  log "composer test:db"
  composer test:db

  log "composer test:security"
  composer test:security

  log "composer test:phpunit"
  composer test:phpunit

  log "composer test:behat:api"
  composer test:behat:api

  log "composer test:behat:front"
  composer test:behat:front
}

export -f appBeforeInstall
export -f appInstall
export -f appScript
