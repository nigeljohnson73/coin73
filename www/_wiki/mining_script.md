# Writing a mining script

This page will outline the process you will need to follow in your language of choice. There will be a selection of semi-supported miners available on the GIT repository shortly.

You will need the following:

 * Your `wallet-id`from your account page;
 * A unique `rig-id` for each miner you want to use on your account.

So the user is not forced to think of something clever and only wants to run one of your script, you can default the `rig-id` to something of your choice, possibly the language it's written in, for example `PHP-Miner`.

Optionally you can use a `chip-id` to help with some hashrate stats we may gather at some point in the project. You should default this to something generic like the language it's written in, for example `PHP Script`. 

## Bring your own miner (BYOM)

Whatever language you write your miner in, there should be as few dependancies as possible for simplicity and debugging reasons. Also remember that this is not about showing the world how clever you are or how awesome your coding skills are. It is about showing people how this works, ensuring the code is easily to follow and well documented.

### Gathering details

How your miner will work will determine how you get the info from the user. If you have a GUI you can ask the user to fill in every time or have a settings function that saves them away. If it's a command line script, take paramters or refer to an ini file - prefereably wwritten in JSON. If you are writing for an embedded controller, then you are probably limited to hard coding values along side the WiFi key. Just keep things as simple for the user as possible. 

You should default the `rig-id` and `chip-id` so the user doesn't need to supply them, but can override them if they want to.

### The work

Here is the process you should repeat:

 * [Request a job](/wiki/api/job/request) via the API;
 * This will give you a `hash`, a `difficulty` and a `target_seconds` value;
 * Iterate through [adding a counter](/wiki/mining/work) on to the end of that string, starting at zero;
 * Calculate the SHA1 hash of that new string;
 * If the SHA1 starts with `difficulty` zeros, that counter value is the `nonce` you will submit;
 * Calculate the `hashrate` as the `nonce` plus 1, divided by the number of seconds it took to get there;
 * Wait for the remainder of the `target_seconds` window;
 * [Submit the job](/wiki/api/job/submit) via the API.
