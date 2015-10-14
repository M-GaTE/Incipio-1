#!/usr/bin/env bash
#
# Functions used to setup the required configuration for the coverage.

CODACY_COVERAGE_REPORT="dist/reports/phpunit/coverage.xml"
SCRUTINIZER_COVERAGE_REPORT="dist/reports/phpunit/coverage.xml"

setupCoverage() {
  if [[ "5.6" = "$TRAVIS_PHP_VERSION" ]]; then
    if [[ -n "$CODACY_PROJECT_TOKEN" ]]; then
      log "Setting coverage configuration for Codacy"
      composer global require codacy/coverage --no-update;
    fi;

    log "Setting coverage configuration for Scrutinizer"
    wget https://scrutinizer-ci.com/ocular.phar;
  fi;
}

sendCoverageData() {
  if [[ "5.6" = "$TRAVIS_PHP_VERSION" ]]; then
    sendCodacyCoverageData
    sendScrutinizerCoverageData
  fi;
}

sendCodacyCoverageData() {
  log "Sending Codacy coverage data"

  if [[ ! -n "$CODACY_PROJECT_TOKEN" ]]; then
    log "Codacy API token not defined. Aborting" --error

    exit 0
  fi;

  if [[ ! -f "$CODACY_COVERAGE_REPORT" ]]; then
    log "Could not send data: file ${CODACY_COVERAGE_REPORT} not found" --error

    exit 0
  fi;

  codacycoverage clover dist/reports/phpunit/coverage.xml
  log "Done" --success
}

sendScrutinizerCoverageData() {
  log "Sending Scrutinizer coverage data"

  if [[ ! -f "$SCRUTINIZER_COVERAGE_REPORT" ]]; then
    log "Could not send data: file ${SCRUTINIZER_COVERAGE_REPORT} not found" --error

    exit 0
  fi;

  php ocular.phar code-coverage:upload --format=php-clover dist/reports/phpunit/coverage.xml;
  log "Done" --success
}

export -f setupCoverage
export -f sendCoverageData
