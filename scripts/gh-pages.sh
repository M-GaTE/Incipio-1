#!/usr/bin/env bash

rev=$(git rev-parse --short HEAD)
remote_url="https://user:${GH_TOKEN}@github.com/in6pio/Incipio.git"

cd dist
git init
git config user.name "TravisBot"
git config user.email "travisbot@incipio.fr"
if git remote | grep origin > /dev/null;
then
  git remote set-url origin "${remote_url}"
else
  git remote add origin "${remote_url}"
fi

git add .
git commit -m "Rebuild pages at ${rev}"
git push --force --quiet origin HEAD:gh-pages
