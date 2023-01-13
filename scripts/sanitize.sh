#!/bin/bash

# MAKE SURE NO LOCAL FILES GET COMMITTED!
# THEN run this script to prepare a new version for the Acas public github repo.

rm -Rf .git

rm -Rf .vscode
rm -Rf podium
rm -Rf tests/e2e
rm -Rf tests/k6_load
rm -Rf migrations

rm .dockerignore
rm bitbucket-pipelines.yml
rm docker-compose.yml
rm Dockerfile
rm messenger-worker.conf
rm playwright.ci.env
rm playwright.config.ts
rm playwright.env
rm renovate.json

current_date_time="`date "+%Y-%m-%d %H:%M:%S"`";

git init
git add .
git commit -m "Prod ${current_date_time} - sanitized code"
git remote add origin git@github.com:AcasDigital/notify-acas.git
# git push origin master
