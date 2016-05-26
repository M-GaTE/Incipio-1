#!/usr/bin/env bash
#
# Functions used to setup the required configuration for the coverage.

CODACY_COVERAGE_REPORT="dist/reports/phpunit/coverage.xml"
SCRUTINIZER_COVERAGE_REPORT="dist/reports/phpunit/coverage.xml"

setupCoverage() {
  if [[ "5.6" = "$TRAVIS_PHP_VERSION" ]]; then
    if [[ -n "$CODACY_PROJECT_TOKEN" ]]; then
      log "Setting up coverage configuration for Codacy"
      echo "global require codacy/coverage --no-update"
      composer global require codacy/coverage --no-update;
    fi;

    log "Setting up coverage configuration for Scrutinizer"
    echo "https://scrutinizer-ci.com/ocular.phar"
    wget --quiet https://scrutinizer-ci.com/ocular.phar;
  fi;
}

sendCoverageData() {
  if [[ "5.6" = "$TRAVIS_PHP_VERSION" ]]; then
    sendCodacyCoverageData
    sendScrutinizerCoverageData
  fi;
}

sendCodacyCoverageData() {
  log "Sending up Codacy coverage data"

  if [[ -z "$CODACY_PROJECT_TOKEN" ]]; then
    log "Codacy API token not defined. Aborting" --error

    return 0
  fi;

  if [[ ! -f "$CODACY_COVERAGE_REPORT" ]]; then
    log "Could not send data: file ${CODACY_COVERAGE_REPORT} not found" --error

    return 0
  fi;

  codacycoverage clover "${CODACY_COVERAGE_REPORT}"
  log "Done" --success
}

sendScrutinizerCoverageData() {
  log "Sending Scrutinizer coverage data"

  if [[ ! -f "$SCRUTINIZER_COVERAGE_REPORT" ]]; then
    log "Could not send data: file ${SCRUTINIZER_COVERAGE_REPORT} not found" --error

    return 0
  fi;

  php ocular.phar code-coverage:upload --format=php-clover "${SCRUTINIZER_COVERAGE_REPORT}";
  log "Done" --success
}

export -f setupCoverage
export -f sendCoverageData
