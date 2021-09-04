# Coin73

The initial drop of Nigels special sauce.

## Interesting URLs:

 * [Configuration files](https://cloud.google.com/appengine/docs/standard/php7/configuration-files)
 * [Authenticating users](https://cloud.google.com/appengine/docs/standard/php7/authenticating-users)
 * [Custom domain](https://cloud.google.com/appengine/docs/standard/php7/mapping-custom-domains)
 * [CRON stuff](https://cloud.google.com/appengine/docs/standard/php7/scheduling-jobs-with-cron-yaml)

## Commands:

 * Launch local for php72: `dev_appserver.py app.yaml --php_executable_path /usr/bin/php --support_datastore_emulator=true`
 * Runtime server: http://localhost:8080
 * Admin server: http://localhost:8000
 * Data store (localhost - if it worked): http://localhost:8000/datastore
 * Data store on GAE: https://console.cloud.google.com/datastore/entities/query/kind?project=coin73

##Things I did

Created a [key for the service account][key-svc-acc] on the project. It downloaded a JSON file, wihch I saved as coin73-service.json


[key-svc-acc]: https://console.cloud.google.com/iam-admin/serviceaccounts/details/118118471124134424927/keys?folder=&organizationId=&project=coin73&supportedpurview=project "Google console page"
