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
 * Iterate through [adding a counter](/wiki/mining/work) on to the end of th string, starting at zero;
 * Calculate the SHA1 hash of that new string;
 * If the SHA1 starts with `difficulty` zeros, that counter value is the `nonce` you will submit;
 * Calculate the `hashrate` as the `nonce` plus 1, divided by the number of seconds it took to get there;
 * Wait for the remainder of the `target_seconds` window;
 * [Submit the job](/wiki/api/job/submit) via the API.

## The official PHP miner
 
If you have PHP 5.5 or higher, and and `php-curl` installed, then this will probably work for you.

 
```
<?php
if (function_exists ( "curl_init" )) {

	// Calls a $url and returns a wrapped object. Pass in $post arguments as key/value array pairs
	function jsonApi($url, $post) {
		$ch = curl_init ( $url );
		curl_setopt ( $ch, CURLOPT_CONNECTTIMEOUT, 0 );
		curl_setopt ( $ch, CURLOPT_TIMEOUT, 60 );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt ( $ch, CURLOPT_POSTFIELDS, $post );

		$data = curl_exec ( $ch );
		$response = curl_getinfo ( $ch, CURLINFO_HTTP_CODE );
		if ($response < 200 || $response > 299) {
			echo "jsonApi(): Got response code $response\n";
			echo "jsonApi(): Data:\n" . $data . "\n";
		}
		$ret = json_decode ( $data );
		curl_close ( $ch );

		return $ret;
	}
} else {
	echo "Unimplimented API calling procedure. Install php-curl and you'll get one for free\n";
	exit ();
}

function help() {
	global $argv;
	echo "\nUsage:- " . basename ( $argv [0] ) . " [-c 'chip-id'] [-d] [-h] [-q] [-r 'rig-id'] -w 'wallet-id' [-y]\n\n";
	echo "    -c 'id' : Set the chip id for this miner (defaults to 'PHP Script')\n";
	echo "    -d      : Use the development server\n";
	echo "    -h      : This help message\n";
	echo "    -q      : Shhhh!!, hide all the 'MESSAGE' output lines\n";
	echo "    -r 'id' : Set the rig name for this miner (defaults to 'PHP-Miner')\n";
	echo "    -w 'id' : Set 130 character wallet ID for miner rewards\n";
	echo "    -y      : Yes!! I got everything correct, just get on with it\n";
	echo "\n";
	exit ();
}

$api_host = "http://coin73.appspot.com/api/";
$rig_id = "PHP-Miner";
$chip_id = "PHP Script";
$wallet_id = "";
$pause = true;
$messages = true;

$opts = getopt ( 'c:dhqr:w:y' );
foreach ( $opts as $k => $v ) {
	if ($k == "c") {
		$chip_id = $v;
	} else if ($k == "d") {
		$api_host = "http://localhost:8085/api/";
	} else if ($k == "h") {
		help ();
	} else if ($k == "q") {
		$messages = false;
	} else if ($k == "r") {
		$rig_id = $v;
	} else if ($k == "w") {
		$wallet_id = $v;
	} else if ($k == "y") {
		$pause = false;
	}
}

if (strlen ( $rig_id ) == 0) {
	echo "No Rig ID supplied\n";
	help ();
}

if (strlen ( $wallet_id ) == 0) {
	echo "No Wallet ID supplied\n";
	help ();
}

if (strlen ( $wallet_id ) != 130) {
	$wid = "04d329153bacfc18f8400b53904729fecbe44637e0b7902254f1a55d1f47b109b1e6d045d45b826234c04e35902eb5423f4b6d6104fde6a05ef3621a86a19f8171";
	echo "Wallet ID doesn't look correct. It should look like this (but don't use this one):\n\n";
	echo "    '" . $wid . "'\n";
	help ();
}

if ($pause) {
	echo "#####################################################################################################################################################\n";
	echo "#\n";
	echo "# PHP Miner\n";
	echo "#\n";
	echo "#    Rig ID    : '" . $rig_id . "'\n";
	echo "#    Wallet ID : '" . $wallet_id . "'\n";
	echo "#    API host  : '" . $api_host . "'\n";
	echo "#    Messages  : " . (($messages) ? ("Enabled") : ("Disabled")) . "\n";
	echo "#\n";
	echo "#####################################################################################################################################################\n";
	echo "Press return to continue\n";
	fgetc ( STDIN );
}

// the production server
function output($are, $hr, $txt = "") {
	global $messages;
	if (strtoupper ( $are ) == "MESSAGE" && ! $messages) {
		return;
	}
	$d = date ( "Y/m/d H:i:s", time () );
	echo $d . "; ";
	echo str_pad ( strtoupper ( $are ), max ( strlen ( "MESSAGE" ), strlen ( "ERROR" ), strlen ( "ACCEPTED" ), strlen ( "REJECTED" ) ) ) . "; ";
	echo str_pad ( (strlen ( $hr ) ? number_format ( $hr, 3 ) : ""), strlen ( "100,000,000.000" ), " ", STR_PAD_LEFT ) . (strlen ( $hr ) ? (" h/s") : ("    ")) . "; ";
	// echo (strlen ( $shares )?("Share "):(" "));
	// echo str_pad ( (strlen ( $shares ) ? number_format ( $shares ) : ""), strlen ( "100,000,000" ), " ", STR_PAD_LEFT ) . "; ";
	echo $txt;
	echo "\n";
}

$rpost = array ();
$rpost ["wallet_id"] = $wallet_id;
$rpost ["rig_id"] = $rig_id;

$spost = array ();
$spost ["hashrate"] = 0;
$spost ["chiptype"] = $chip_id;

$shares = 0;

output ( "MESSAGE", "", "Starting mining operation" );
while ( 1 ) {
	output ( "MESSAGE", "", "Requesting job" );
	$data = jsonApi ( $api_host . "job/request/json", $rpost );

	if (! ($data && $data->success)) {
		output ( "ERROR", "", isset ( $data->reason ) ? ($data->reason) : ("API call failed") );
		// Got an error.
		sleep ( 1 );
	} else {
		// Strip off the call wrapper
		$data = $data->data;

		// Log the start time so we can maximise profit :)
		$started = microtime ( true );

		// Store the job id for sending back later
		$job_id = $data->job_id;

		// Start the counter at zero
		$cnonce = 0;
		$nonce = - 1;

		// Calulate the beginning we need from the difficulty
		$begins = str_pad ( "", $data->difficulty, "0" );

		output ( "MESSAGE", "", "Processing job" );
		// Repeat the looping until we find a valid signature, or we run out of time (being twice the target)
		while ( ($nonce < 0) && (microtime ( true ) < ($started + (2 * $data->target_seconds))) ) {
			// Calculate the signature hash
			$signed = hash ( "sha1", $data->hash . $cnonce );

			// Check if the signature starts with the expected number of zeros
			if (strpos ( $signed, $begins ) === 0) { // If it has, we found one
				$duration = microtime ( true ) - $started;
				$spost ["hashrate"] = ($cnonce+1) / $duration;
				// Set the nonce so we can end this loop
				$nonce = $cnonce;
			}
			$cnonce = $cnonce + 1;
		}

		output ( "MESSAGE", "", "Waiting for submission window" );
		while ( microtime ( true ) < ($started + $data->target_seconds) ) {
			usleep ( 100 );
		}

		output ( "MESSAGE", "", "Submitting job" );
		$data = jsonApi ( $api_host . "job/submit/json/" . $job_id . "/" . (($nonce < 0) ? (0) : ($nonce)), $spost );

		if ($data && $data->success) {
			$shares += 1;
			output ( "ACCEPTED", $spost ["hashrate"], "Share " . number_format ( $shares ) );
		} else {
			output ( "REJECTED", $spost ["hashrate"], isset ( $data->reason ) ? ($data->reason) : ("An API error occurred") );
		}
	}
}
?>
```