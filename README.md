# Coin73

The initial drop of Nigels special sauce.

## Things you gotta do

 * Start 2 terminal windows
 * Goto the main code directory in both terminals
 * `cd ~/git/coin73`
 * In one window start a test server for the API service
 * `php -S localhost:8085 -t api`
 * In the other window, start the test server for the web service
 * `php -S localhost:8080 -t www www/router.php`

## Interesting URLs:

 * [Configuration files](https://cloud.google.com/appengine/docs/standard/php7/configuration-files)
 * [Authenticating users](https://cloud.google.com/appengine/docs/standard/php7/authenticating-users)
 * [Custom domain](https://cloud.google.com/appengine/docs/standard/php7/mapping-custom-domains)
 * [CRON stuff](https://cloud.google.com/appengine/docs/standard/php7/scheduling-jobs-with-cron-yaml)
 * [ReCAPTCHA details](https://www.google.com/recaptcha/admin/site/474517032)

## Commands:

 * ~Launch local for php72: `dev_appserver.py app.yaml --php_executable_path /usr/bin/php --support_datastore_emulator=true`~
 * Runtime server for web-app: http://localhost:8080
 * Runtime server for web-api: http://localhost:8085/api/
 * ~Admin server (not overly useful any more): http://localhost:8000~
 * ~Data store (localhost - if it worked): http://localhost:8000/datastore~
 * Data store on GAE: https://console.cloud.google.com/datastore/entities/query/kind?project=coin73

##Things I did

 * Created a [key for the service account][key-svc-acc] on the project. It downloaded a JSON file, wihch I saved as service-account.json
 * ~optionally, well done one, install the local datastore emulator with `gcloud components install cloud-datastore-emulator`~
 * ~Start the local data store `gcloud beta emulators datastore start`~

[key-svc-acc]: https://console.cloud.google.com/iam-admin/serviceaccounts/details/118118471124134424927/keys?folder=&organizationId=&project=coin73&supportedpurview=project "Google console page"

