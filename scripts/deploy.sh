#!/usr/bin/env bash
#
# Functions for publishing and deploying artefacts

configTravis() {
  isDeployBuild
  if [[ 0 = "$?" ]]; then
    log "No deployment scheduled for this build"

    return 0;
  fi

  log "Prepare deployment for this build";

  log "Configure Git user";
  if [[ -z "$GITHUB_USER_NAME" ]] || [[ -z "$GITHUB_USER_EMAIL" ]]; then
    log "Could not configure Git user" --error
  else
    GITHUB_USER_NAME="TravisBot"
    GH_USER_EMAIL="travisbot@incipio.fr"
  fi

  log "Install necessary packages";
  pip install ghp-import --user
  pip install mkdocs --user
  composer global require halleck45/phpmetrics --no-update
  composer global require apigen/apigen --no-update

  log "Deployment configuration read" --success

  return 0
}

export -f configTravis
