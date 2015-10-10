#!/usr/bin/env bash
#
# Functions used to setup the required configuration for the coverage.

setupCoverage() {
  if [[ "5.6" = "$TRAVIS_PHP_VERSION" ]]; then
    if [[ -n "$CODACY_PROJECT_TOKEN" ]]; then
      log "Setup coverage for Codacy"
      composer global require codacy/coverage --no-update;
    fi;

    log "Setup coverage for Scrutinizer"
    wget https://scrutinizer-ci.com/ocular.phar;
  fi;
}

sendCoverageData() {
  if [[ "5.6" = "$TRAVIS_PHP_VERSION" ]]; then
    if [[ -n "$CODACY_PROJECT_TOKEN" ]]; then
      codacycoverage clover dist/reports/phpunit/coverage.xml
      log "Send coverage report to Codacy" --success
    fi;

    php ocular.phar code-coverage:upload --format=php-clover dist/reports/phpunit/coverage.xml;
    log "Send coverage report to Scrutinizer" --success
  fi;
}

export -f setupCoverage
export -f sendCoverageData
