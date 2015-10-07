#!/usr/bin/env bash
#
# Functions for publishing reports and docs to GitHub Pages

publishToGithubPages() {
  isDeployBuild
  if [[ 0 = "$?" ]]; then
    log "Skipped"

    return 0
  fi

  log "Retrieve GitHub Pages website"
  if [[ -z "$GITHUB_PAGES_REMOTE" ]]; then
    log "Could not retrieve GitHub Pages website. Abort GitHub Pages deployment" --error

    return 1;
  fi
  git clone "$GITHUB_PAGES_REMOTE" gh-pages --branch=gh-pages --single-branch

  log "Generates PhpMetrics reports (x3)"
  phpmetrics --report-html=dist/reports/phpmetrics/app.html src
  phpmetrics --report-html=dist/reports/phpmetrics/api-bundle.html src/ApiBundle
  phpmetrics --report-html=dist/reports/phpmetrics/front-bundle.html src/FrontBundle

  log "Generates PHPDoc"
  apigen generate --source src --destination dist/api-doc

  log "Build GitHub Pages website"
  mkdocs build --clean

  log "Publish artefacts to GitHub Pages"
  mv -f dist/api-doc gh-pages
  mv -f dist/reports gh-pages

  ghp-import -m "$GITHUB_PAGES_COMMIT_MESSAGE" -r "$GITHUB_PAGES_REMOTE" -p gh-pages

  log "Artefacts published" --success

  return 0
}

export -f publishToGithubPages
