# Coin73 Miners

Here we have a few example miners. They will work in you follow the instructions. You will also need the following:

 * Your wallet ID from your main account page
 * A rig-id for each miner you want to use
 * Optionally, a chip ID  - some way to identify the hardware you are using for information gathering and tuning later

## PHP miner

### pre-requestites

 * Currently CURL is needed to make the API calls. Ensure you have php-curl installed.
 * PHP 5 and up should work. Tested on PHP 7.4

### Operation

The script runs from the command line amd the `-h` flag will display the help:

```
bash-3.2$ php miner.php -h

Usage:- miner.php [-c 'chip-id'] [-d] [-h] [-q] [-r 'rig-id'] -w 'wallet-id' [-y]

    -c 'id' : Set the chip id for this miner (defaults to 'PHP Script')
    -d      : Use the development server
    -h      : This help message
    -q      : Shhhh!!, hide all the 'MESSAGE' lines
    -r 'id' : Set the rig name for this miner (defaults to 'PHP-Miner')
    -w 'id' : Set 130 charagter wallet ID for miner rewards
    -y      : Yes, everything is correct, just get on with it

bash-3.2$ 
```

You should supply the wallet ID from your dashboard. You can optionally supply a rig-id of your choosing, and if you want to help some of the metrics, a chip-id as well.


## Python miner

Coming soon - once I've learned enough Python and worked it out.

## ESP32 miner

Coming soon - once I've worked it out.

## ESP8266 miner

Coming soon - once I've worked it out.

## AVR miner (Arduino Nano)

Coming soon - once I've worked it out.

## Other miners

Coming soon - once someone else has worked it out :)
