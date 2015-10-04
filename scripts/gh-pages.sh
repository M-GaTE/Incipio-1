#!/usr/bin/env bash

rev=$(git rev-parse --short HEAD)

cd dist
git init
git config user.name "TravisBot"
git config user.email "travisbot@incipio.fr"
git rm origin
git add origin https://user:${GH_TOKEN}@github.com/user/repo.git ${GH_HOME}

git add .
git commit -m "Rebuild pages at ${rev}"
git push --force --quiet origin HEAD:gh-pages
