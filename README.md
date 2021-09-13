# Coin73

The codename for Nigels new Blockchain. The name will be changing changing but it's core will not.

## Overview

COIN73 is a ground up implementation of a secure centralised block chain, supporting a crypto currency and some useful stuff in the chain (to be developed later - maybe NFT, maybe research data, don't know yet). I was originally inspired in part by [Duino-Coin](https://github.com/revoxhere/duino-coin), and I have also tried to build on some of the more mainstream ideas from Bitcoin and other 'regular' Blockchains. In summary this is the direction I am hoping to take:

 * Secure by design web portal for account management;
 * Periodic proof of life checks to ensure users still exist;
 * Automatic sideways load scaling that grows with the project;
 * Light-weight miner API and mining process;
 * If it's got a microcontroller on it, it should be able to mine;
 * Dual layer 'value' - ledgering blockchain and mining, but then something else as well;
 * Having some real dollar value/liquidity by 2026 (a 5 year plan).
 
 On that last point, please feel free to donate and help keep the lights on.
 
I also want to flatten the miner curve. In Bitcoin, Etherium etc, it's a race... the bigger your rig, the faster you can process stuff, the richer **you** get, and then the harder it is for everyone else to even get in the door. If you play nicely here, we're all friends and everyone benefits. If you violate the rules or try and scam the system, the centralised overlord will ignore you at best, or drive a karma bus at you: either way you'll be wasting your own energy.

There are no claims of enviornmental friendliness, or favouring lower power devices - everyone can play. If you have a low power device that can process the work, then the rewards may be, relatively speaking, higher. Since there is no real money here, there will never be any 'profit'.

This project deals specifically with the centralised services. Mining will be a separate project coming along once the server stuff is stable.

## Things harmed in the making of this project

This project is built on the Google App Engine platform. The main idea here is that you pay for what you use, so you're not wasting resource paying for servers that are only 30% loaded. If you do have a spike or grow a lot, Google can throw more resource at things for you while you sleep. I appreciate there are huge caveats here. But this is a journey.

### General technologies

 * [Bootstrap v5.1](https://getbootstrap.com/docs/5.1)
 * [Boostrap Icons 1.5](https://icons.getbootstrap.com/)
 * [AngularJS v1.8](https://code.angularjs.org/1.8.2/docs/tutorial)
 * [HighlightJS](https://highlightjs.org)
 * [Google App Engine for PHP 7](https://cloud.google.com/appengine/docs/standard/php7/tutorials)
 * [Google DataStore](https://cloud.google.com/datastore)
 * [Google FileStore](https://cloud.google.com/filestore)
 * [Google RECAPTCHA v3](https://developers.google.com/recaptcha/docs/v3)

### Library dependancies

 * [Slim Dispatch Handler v4](https://www.slimframework.com/docs/v4/)
 * [PHPMailer](https://github.com/PHPMailer/PHPMailer)
 * [PHP Markdown](https://github.com/michelf/php-markdown)
 * [PHP Recaptcha](https://github.com/google/recaptcha)
 * [PHP Google DataStore API](https://github.com/tomwalder/php-gds)
 * [PHP Eliptic Curve Cryptography](https://github.com/phpecc/phpecc)

-------------------------------------------------------------------------------------------

# I haven't go round to rationalising things below here yet

## Up and running on the DevServer

You shouldn't need to do this on the PI cuz it's setup in the crontab. This is for the install on the Mac.

 * Start 2 terminal windows
 * Goto the main code directory in both terminals
 * `cd ~/git/coin73` 
 * In one window start a test server for the API service
 * `php -S localhost:8085 -t api api/index.php`
 * In the other window, start the test server for the web service
 * `php -S localhost:8080 -t www www/index.php`

## Project URLs:

 * [COIN73 github code](https://github.com/nigeljohnson73/coin73)
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

##Things I did

 * Setup an [app password][gmail-app-password] for the gmail account.
 * Follow the [RECAPTCHA integration][recaptcha-integration] documentation.
 * ~Created a [key for the service account](https://console.cloud.google.com/iam-admin/serviceaccounts/details/118118471124134424927/keys?folder=&organizationId=&project=coin73&supportedpurview=project) on the project. It downloaded a JSON file, wihch I saved as service-account.json~
 * ~Launch local for php72: `dev_appserver.py app.yaml --php_executable_path /usr/bin/php --support_datastore_emulator=true`~
 * ~optionally, well done one, install the local datastore emulator with `gcloud components install cloud-datastore-emulator`~
 * ~Start the local data store `gcloud beta emulators datastore start`~

Install php 7.4 On a Mac:

 * brew install php@7.4
 * brew link php@7.4
 * (also does this any way - brew install gmp)
 

[recaptcha-integration]: https://code.tutsplus.com/tutorials/example-of-how-to-add-google-recaptcha-v3-to-a-php-form--cms-33752
[gmail-app-password]: https://support.google.com/accounts/answer/185833?p=InvalidSecondFactor&visit_id=637667920918322961-3041154280&rd=1