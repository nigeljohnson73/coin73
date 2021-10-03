#!/bin/sh

cd /webroot/coin73
gcloud app deploy dispatch.yaml www api cron cron.yaml
gcloud datastore indexes create index.yaml --quiet
gcloud datastore indexes cleanup index.yaml --quiet