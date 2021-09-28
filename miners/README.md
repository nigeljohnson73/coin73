# Coin73 Miners

All pre-requesites in all of the miners assume the user has an account setup to use the miners. Users will need the following:

 * Your `wallet-id`from your account page;
 * A unique `rig-id` for each miner you want to use on your account.

So the user is not forced to think of something clever and only wants to run one of your script, you can default the `rig-id` to something of your choice, possibly the language it's written in, for example `PHP-Miner`.

Optionally you can use a `chip-id` to help with some hashrate stats we may gather at some point in the project. You should default this to something generic like the language it's written in, for example `PHP Script`. 

Also supplied below are a few 'supported' miners built here. They will work in you follow the instructions... which might not be fully written...

Let's start with how you write your own.

## Bring your own miner (BYOM)

Whatever language you write your miner in, there should be as few dependancies as possible for simplicity and debugging reasons. Also remember that this is not about showing the world how clever you are or how awesome your coding skills are. It is about showing people how this works, ensuring the code is easily to follow and well documented.

### Gathering details

How your miner will work will determine how you get the info from the user. If you have a GUI you can ask the user to fill in every time or have a settings function that saves them away. If it's a command line script, take paramters or refer to an ini file - prefereably written in JSON. If you are writing for an embedded controller, then you are probably limited to hard coding values along side the WiFi key. Just keep things as simple for the user as possible. 

You should default the `rig-id` and `chip-id` so the user doesn't need to supply them, but can override them if they want to.

### The work

Here is the process you should repeat:

 * [Request a job](https://coin73.appspot.com/wiki/api/job/request) via the API;
 * This will give you a `hash`, a `difficulty` and a `target_seconds` value;
 * Iterate through [adding a counter](https://coin73.appspot.com/wiki/mining/work) on to the end of the `hash` string, starting at zero;
 * Calculate the SHA1 hash of the new string (with the counter added);
 * If the SHA1 starts with `difficulty` zeros, that counter value is the `nonce` you will submit;
 * Calculate the `hashrate` as the `nonce` plus 1, divided by the seconds it took to get there;
 * Wait for the remainder of the `target_seconds` window;
 * [Submit the job](https://coin73.appspot.com/wiki/api/job/submit) via the API.

# PHP miner

## pre-requestites

 * Currently CURL is needed to make the API calls. Ensure you have `php-curl` installed.
 * PHP 5 and up should work. Tested on PHP 7.4

## Operation

The script runs from the command line and the `-h` flag will display the help:

```language-console
bash-3.2$ php miner.php -h

Usage:- miner.php [-c 'chip-id'] [-d] [-h] [-q] [-r 'rig-id'] -w 'wallet-id' [-y]

    -c 'id' : Set the chip id for this miner (defaults to 'PHP Script')
    -d      : Use the development server
    -h      : This help message
    -q      : Shhhh!!, hide all the 'MESSAGE' output lines
    -r 'id' : Set the rig name for this miner (defaults to 'PHP-Miner')
    -w 'id' : Set 130 character wallet ID for miner rewards
    -y      : Yes!! I got everything correct, just get on with it

bash-3.2$ 
```

As a minimum, supply the wallet ID from your dashboard. You can optionally supply a `rig-id` of your choosing, and if you want to help some of the metrics, a `chip-id` as well.

```language-console
bash-3.2$ php miner.php -w 04d329153bacfc18f8400b......
```

# Python miner

Coming soon - once I've learned enough Python and worked it out.

# ESP32 miner

## pre-requestites

 * You should probably use the Arduino IDE, that is what this has all been tested on;
 * You need an up to date implementation of the ESP32 firmware, add ththe following to your board manager list;
 * https://raw.githubusercontent.com/espressif/arduino-esp32/gh-pages/package_esp32_dev_index.json
 * You should then be able to select the ESP32 boards version 2.0.0 or higher within the board manager;
 * The code has been written to have a few dependancies as possibl, so it uses the textual API - no JSON.

Once all of that is done and software compiles, you will need to update a few varaibles that are 'hard-coded' near the top of the file.

```language-c
const char* wifi_ssid = "WIFISSID";  // You WiFi SSID on your router
const char* wifi_pass = "PASSWORD";  // The password/phrase you access the router with

const char* wallet_id = "WALLETID";  // The really long string found on the account page
const char* rig_id  = "ESP32-Miner"; // This must be unique on your account
```

Compile it, upload it to your ESP32 and let it run.

## Operation

The miner will just run all by itself as there is no 'interface' for it. As long as it has power, and connection to the internet via your router all is good in the world.

# ESP8266 miner

Coming soon - once I've worked it out.

# AVR miner (Arduino Nano)

Coming soon - once I've worked it out.

# Other miners

Coming soon - once someone else has worked it out :)
