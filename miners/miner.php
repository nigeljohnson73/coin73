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
	exit();
}

function help() {
	global $argv;
	echo "\nUsage:- " . basename ( $argv [0] ) . " [-c 'chip-id'] [-d] [-h] [-r 'rig-id'] -w 'wallet-id' [-y]\n\n";
	echo "    -c 'id' : Set the chip id for this miner (defaults to 'PHP Script')\n";
	echo "    -d      : Use the development server\n";
	echo "    -h      : This help message\n";
	echo "    -r 'id' : Set the rig name for this miner (defaults to 'PHP-Miner')\n";
	echo "    -w 'id' : Set 130 charagter wallet ID for miner rewards\n";
	echo "    -y      : Yes, everything is correct, just get on with it\n";
	echo "\n";
	exit ();
}

$api_host = "http://coin73.appspot.com/api/";
$rig_id = "PHP-Miner";
$chip_id = "PHP Script";
$wallet_id = "";
$pause = true;

$opts = getopt ( 'c:dhr:w:y' );
foreach ( $opts as $k => $v ) {
	if ($k == "c") {
		$chip_id = $v;
	} else if ($k == "d") {
		$api_host = "http://localhost:8085/api/";
	} else if ($k == "h") {
		help ();
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
	echo "#    API       : '" . $api_host . "'\n";
	echo "#\n";
	echo "#####################################################################################################################################################\n";
	echo "Press return to continue\n";
	fgetc ( STDIN );
}

// the production server
function output($are, $hr, $txt = "") {
	$d = date ( "Y/m/d H:i:s", time () );
	echo $d . "; ";
	echo str_pad ( strtoupper($are), max ( strlen ( "MESSAGE" ), strlen ( "ERROR" ), strlen ( "ACCEPTED" ), strlen ( "REJECTED" ) ) ) . "; ";
	echo str_pad ( (strlen ( $hr ) ? number_format ( $hr, 3 ) : ""), strlen ( "100,000,000.000" ), " ", STR_PAD_LEFT ) . (strlen($hr)?(" h/s"):("    "))."; ";
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
				$spost ["hashrate"] = $cnonce / $duration;
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