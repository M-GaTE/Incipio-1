#!/usr/bin/env bash

echo -en $(cat << EOF
\e[1;33m\n
Publishing artefacts to GitHub Pages script.\n
\e[0m\n
Reporters:\n
  name: ${GH_USER_NAME}\n
  email: ${GH_USER_EMAIL}\n
\n
EOF
);

git clone "${GH_REMOTE}" gh-pages --single-branch --branch=gh-pages
cd gh-pages
git config user.name "${GH_USER_NAME}"
git config user.email "${GH_USER_EMAIL}"
rm -rf reports
mv ../dist/reports .
git add --all
git commit --message "${GH_COMMIT_MESSAGE}" --quiet
git push --force origin gh-pages

echo -en "\e[1;32mArtefacts published.\e[0m"
