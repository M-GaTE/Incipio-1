#!/usr/bin/env bash

# Config
git clone "${GH_REMOTE}" gh-pages --single-branch --branch=gh-pages
cd gh-pages
git config user.name "${GH_USER_NAME}"
git config user.email "${GH_USER_EMAIL}"

# Cleanup
rm -rf api-doc
rm -rf reports

# Retrieve artefacts
mv ../dist/api-doc .
mv ../dist/reports .

# Publish
git add --all
git commit --message "${GH_COMMIT_MESSAGE}" --quiet
git push --force origin gh-pages

echo -en "\e[1;32mArtefacts published.\e[0m"
