# Coin73

The initial drop of Nigels special sauce.

## Things you gotta do every time

 * Start 2 terminal windows
 * Goto the main code directory in both terminals
 * `cd ~/git/coin73` on Mac
 * In one window start a test server for the API service
 * `php -S localhost:8085 -t api`
 * In the other window, start the test server for the web service
 * `php -S localhost:8080 -t www www/router.php`

## Things you gotta do Once

Install php 7.4 On a Mac:

 * brew install php@7.4
 * brew link php@7.4
 * (also does this any way - brew install gmp)
 
Install php 7.4 on Raspian:

 * https://janw.me/raspberry-pi/installing-php74-rapsberry-pi/
 * Install ext-gmp into PHP somehow

Setup an app passsword for your email account

* https://support.google.com/accounts/answer/185833?p=InvalidSecondFactor&visit_id=637667920918322961-3041154280&rd=1

## Project URLs:

 * [COIN73 on github](https://github.com/nigeljohnson73/coin73)
 * [COIN73 on the web](https://coin73.appspot.com)
 * [localhost web-app](http://localhost:8080)
 * [localhost web-api](http://localhost:8085/api/) (not really useful)
 * [Data store on GAE](https://console.cloud.google.com/datastore/entities/query/kind?project=coin73)
 * [ReCAPTCHA details](https://www.google.com/recaptcha/admin/site/474517032)

## Blockchain in Javascript:

 * [Part 1 - Create Blockchain](https://www.youtube.com/watch?v=zVqczFZr124)
 * [Part 2 - Proof of Work](https://www.youtube.com/watch?v=HneatE69814)
 * [Part 3 - Mining rewards](https://www.youtube.com/watch?v=fRV6cGXVQ4I)
 * [Part 4 - Signing transactions](https://www.youtube.com/watch?v=kWQ84S13-hw)

## Interesting URLs:

 * [YAML Configuration files](https://cloud.google.com/appengine/docs/standard/php7/configuration-files)
 * [Authenticating users on google](https://cloud.google.com/appengine/docs/standard/php7/authenticating-users)
 * [Custom domain for appspot](https://cloud.google.com/appengine/docs/standard/php7/mapping-custom-domains)
 * [GAE CRON stuff](https://cloud.google.com/appengine/docs/standard/php7/scheduling-jobs-with-cron-yaml)
 * [Bootstrap 5.1 documentation](https://getbootstrap.com/docs/5.1/getting-started/introduction/)
 * [Eliptic Curve Cryptography](https://github.com/simplito/elliptic-php)

##Things I did

 * Followed the [RECAPTCHA integration][recaptcha-integration] documentation.
 * Created a [key for the service account][key-svc-acc] on the project. It downloaded a JSON file, wihch I saved as service-account.json
 * ~Launch local for php72: `dev_appserver.py app.yaml --php_executable_path /usr/bin/php --support_datastore_emulator=true`~
 * ~optionally, well done one, install the local datastore emulator with `gcloud components install cloud-datastore-emulator`~
 * ~Start the local data store `gcloud beta emulators datastore start`~

[key-svc-acc]: https://console.cloud.google.com/iam-admin/serviceaccounts/details/118118471124134424927/keys?folder=&organizationId=&project=coin73&supportedpurview=project "Google console page"
[recaptcha-integration]: https://code.tutsplus.com/tutorials/example-of-how-to-add-google-recaptcha-v3-to-a-php-form--cms-33752
