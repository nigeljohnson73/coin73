<?php
//@formatter:off
/*
 _______ _____ __   _ _______  ______     _______  _____   ______
 |  |  |   |   | \  | |______ |_____/ ___    |    |     | |_____/
 |  |  | __|__ |  \_| |______ |    \_        |    |_____| |    \_
                                                                 
  _____  _     _  _____       _______ _____ __   _ _______  ______
 |_____] |_____| |_____]      |  |  |   |   | \  | |______ |_____/
 |       |     | |            |  |  | __|__ |  \_| |______ |    \_ 

                                                       Version 0.1a
 (c) Nigel Johnson 2020
 https://github.com/nigeljohnson73/coin73
*/
// @formatter:on
$VERSION = "0.1a";
$use_tor = true;

if (function_exists ( "curl_init" )) {

	// Calls a $url and returns a wrapped object. Pass in $post arguments as key/value array pairs
	function jsonApi($url, $post) {
		global $use_tor, $tor_proxy;
		$ch = curl_init ( $url );
		curl_setopt ( $ch, CURLOPT_CONNECTTIMEOUT, 0 );
		curl_setopt ( $ch, CURLOPT_TIMEOUT, 60 );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt ( $ch, CURLOPT_POSTFIELDS, $post );
		if ($use_tor) {
			curl_setopt ( $ch, CURLOPT_PROXY, $tor_proxy );
			curl_setopt ( $ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5_HOSTNAME );
		}
		$data = curl_exec ( $ch );
		$response = curl_getinfo ( $ch, CURLINFO_HTTP_CODE );
		if ($response < 200 || $response > 299) {
			echo "jsonApi(): Got response code $response\n";
			echo "jsonApi(): Data:\n" . $data . "\n";
		}
		$ret = json_decode ( $data );
		curl_close ( $ch );

		return $ret;
		// }
	}
} else {
	echo "Unimplimented API calling procedure. Install php-curl and you'll get one for free\n";
	exit ();
}

function help() {
	global $argv;
	echo "\nUsage:- " . basename ( $argv [0] ) . " [-c 'chip-id'] [-d] [-h] [-p 'tor-proxy'] [-q] [-r 'rig-id'] -w 'wallet-id' [-y]\n\n";
	echo "    -c 'id'  : Set the chip id for this miner (defaults to 'PHP Script')\n";
	echo "    -d       : Use the development server (mnrtor.local)\n";
	echo "    -h       : This help message\n";
	echo "    -p 'url' : Set the TOR proxy (defaults to '127.0.0.1:9050')\n";
	echo "    -r 'id'  : Set the rig name for this miner (defaults to 'PHP-Miner')\n";
	echo "    -w 'id'  : Set 130 character wallet ID for miner rewards\n";
	echo "    -y       : Yes!! I got everything correct, just get on with it\n";
	echo "\n";
	exit ();
}

// $api_host = "http://coin73.appspot.com/api/";
$api_host = "http://ckwtzols3ukgmnam5w2bixq3iyw6d5oedp7a5cli6totg6ektlyknsqd.onion/api/";
$rig_id = "PHP-Miner";
$chip_id = "PHP Script";
$tor_proxy = "127.0.0.1:9050";
$wallet_id = "";
$pause = true;

$opts = getopt ( 'c:dhp:r:w:y' );
foreach ( $opts as $k => $v ) {
	if ($k == "c") {
		$chip_id = $v;
	} else if ($k == "d") {
		$api_host = "http://mnrtor.local/api/";
		$use_tor = false;
	} else if ($k == "h") {
		help ();
	} else if ($k == "p") {
		$tor_proxy = $v;
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
	echo "Wallet ID doesn't look correct. It should look like this (but obviously, don't use this one):\n\n";
	echo "    '04d329153bacfc18f8400b53904729fecbe44637e0b7902254f1a55d1f47b109b1e6d045d45b826234c04e35902eb5423f4b6d6104fde6a05ef3621a86a19f8171'\n";
	help ();
}

if ($pause) {
	echo "#####################################################################################################################################################\n";
	echo "#\n";
	echo "# PHP Miner v" . $VERSION . "\n";
	echo "#\n";
	echo "#    Rig ID    : '" . $rig_id . "'\n";
	echo "#    Wallet ID : '" . $wallet_id . "'\n";
	echo "#    API host  : '" . $api_host . "'\n";
	echo "#    TOR proxy : '" . $tor_proxy . "'\n";
	echo "#\n";
	echo "#####################################################################################################################################################\n";
	echo "Press return to continue\n";
	fgetc ( STDIN );
}

// Output messages with timestamp
function output($txt = "") {
	$d = date ( "Y/m/d H:i:s", time () );
	echo $d . " | ";
	echo $txt;
	echo "\n";
}

// Set up the data for the request job API
$rpost = array ();
$rpost ["wallet_id"] = $wallet_id;
$rpost ["rig_id"] = $rig_id;

// Prepare the data for the submit job API (hashrate will be calulated and overwritten)
$spost = array ();
$spost ["hashrate"] = 0;
$spost ["chiptype"] = $chip_id;

// KEep track of how many jobs we received and how many were successful
$job_c = 0;
$shares = 0;

// Loop forever (hopefully)
while ( true ) {
	// Request a job
	$data = jsonApi ( $api_host . "job/request/json", $rpost );
	if (! ($data && $data->success)) {
		// Got an error. Pause in case the server is struggling
		output ( "0x00 | Request failed " . isset ( $data->reason ) ? ($data->reason) : ("API call failed") );
		sleep ( 5 );
	} else {
		// Log the start time so we can maximise profit :)
		$started = microtime ( true );

		// Increment total job received count
		$job_c += 1;

		// Strip off the API call wrapper
		$data = $data->data;

		// Store the job id for sending back later
		$job_id = $data->job_id;

		// Start the counter at zero
		$cnonce = 0;
		$nonce = - 1;

		// Calulate the beginning we need for a 'successful' job from the difficulty
		$begins = str_pad ( "", $data->difficulty, "0" );

		// Output the job details in the Text API format
		output ( "0x01 | Received job: Y " . $job_id . " " . $data->hash . " " . str_pad ( $data->difficulty, 2, "0", STR_PAD_LEFT ) . " " . str_pad ( $data->target_seconds, 2, "0", STR_PAD_LEFT ) );

		// Repeat the looping until we find a valid signature, or we run out of time (being twice the target)
		while ( ($nonce < 0) && (microtime ( true ) < ($started + (2 * $data->target_seconds))) ) {
			// Calculate the signature hash
			$signed = hash ( "sha1", $data->hash . $cnonce );

			// Check if the signature starts with the expected number of zeros
			if (strpos ( $signed, $begins ) === 0) {
				// Set the nonce so we can end this loop
				$nonce = $cnonce;
				// calcuate the hashrate
				$duration = microtime ( true ) - $started;
				$spost ["hashrate"] = ($cnonce + 1) / $duration;
			}

			// Increment the counter so we can try again
			$cnonce = $cnonce + 1;
		}

		// If we calcuated a value, tell everyone
		if ($nonce >= 0) {
			output ( "0x02 | Nonce: " . $nonce . " | duration: " . number_format ( $duration, 4 ) . " | hashrate: " . number_format ( $spost ["hashrate"], 2 ) . " | hash: " . $signed );
		} else {
			output ( "0x02 | Error: Failed to calculate hash in time" );
		}

		// Wait for the submission window
		while ( microtime ( true ) < ($started + $data->target_seconds) ) {
			usleep ( 100 );
		}

		// Submit the job, even if we failed - it's ok to admit failure.
		$data = jsonApi ( $api_host . "job/submit/json/" . $job_id . "/" . (($nonce < 0) ? (0) : ($nonce)), $spost );
		if ($data && $data->success) {
			$shares += 1;
			output ( "0x03 | ACCEPTED | " . number_format ( $shares ) . "/" . number_format ( $job_c ) . " | " . number_format ( ($shares / $job_c) * 100, 2 ) . "%" );
		} else {
			output ( "0x04 | REJECTED | " . (isset ( $data->reason ) ? ($data->reason) : ("An API error occurred")) );
			sleep ( 1 );
		}
	}
}
?>