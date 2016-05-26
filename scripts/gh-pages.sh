#!/usr/bin/env bash
#
# Functions for publishing reports and docs to GitHub Pages

publishToGithubPages() {
  isDeployBuild
  if [[ 0 = "$?" ]]; then
    log "Skipped"

    return 0
  fi

  log "Retrieving GitHub Pages website"
  if [[ -z "$GITHUB_PAGES_REMOTE" ]]; then
    log "Could not retrieve GitHub Pages website. Abort GitHub Pages deployment" --error

    return 1;
  fi
  git clone "$GITHUB_PAGES_REMOTE" gh-pages --branch=gh-pages --single-branch

  log "Generating PhpMetrics reports (x3)"
  echo "phpmetrics --report-html=dist/reports/phpmetrics/app.html src"
  phpmetrics --report-html=dist/reports/phpmetrics/app.html src
  echo "phpmetrics --report-html=dist/reports/phpmetrics/api-bundle.html src/ApiBundle"
  phpmetrics --report-html=dist/reports/phpmetrics/api-bundle.html src/ApiBundle
  echo "phpmetrics --report-html=dist/reports/phpmetrics/front-bundle.html src/FrontBundle"
  phpmetrics --report-html=dist/reports/phpmetrics/front-bundle.html src/FrontBundle

  log "Generating PHPDoc"
  echo "apigen generate --source src --destination dist/api-doc"
  apigen generate --source src --destination dist/api-doc

  log "Building GitHub Pages website"
  echo "mkdocs build --clean"
  mkdocs build --clean

  log "Publishing artefacts to GitHub Pages"
  echo "mv -f dist/api-doc gh-pages"
  mv -f dist/api-doc gh-pages
  echo "mv -f dist/reports gh-pages"
  cp -rf dist/reports gh-pages

  cd gh-pages
  git add --all
  git commit --quiet --message "$GITHUB_PAGES_COMMIT_MESSAGE"
  git push --force origin gh-pages
  cd ..

  log "Done" --success
}

export -f publishToGithubPages
