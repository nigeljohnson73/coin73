<?php
// This api is called when the the user has requested a user creation providing username/password, toc approval as well as recaptcha details in $_POST
//
$ret = startJsonResponse ();

echo "ARGS:\n";
print_r ( $args );
echo "_POST[]:\n";
print_r ( $_POST );

$success = false;
$message = "";

$ret->data = new StdClass ();

if (isset ( $args ["job_id"] ) && isset ( $args ["nonce"] )) {
	$hashrate = isset ( $_POST ["hashrate"] ) ? $_POST ["hashrate"] : 0;
	$chiptype = isset ( $_POST ["chiptype"] ) ? $_POST ["chiptype"] : "";

	$store = new JobStore ();

	$arr = $store->getItemById ( $args ["job_id"] );
	if (is_array ( $arr )) {
		$arr = $store->delete ( $arr );
		if (is_array ( $arr )) {
			$delta = (msTime () - $arr ["created"]);
			if ($delta >= minerSubmitMinSeconds ( $arr ["wallet_id"] ) && ($delta <= minerSubmitMaxSeconds ( $arr ["wallet_id"] ))) {

				$hash = hash ( "sha1", $arr ["hash"] . $args ["nonce"] );
				$starts = str_pad ( "", 2, "0" );
				if (strpos ( $hash, $starts ) === 0) {
					// look up any block with that hash,
					// set it up with the details.
					// Calculate the share
					// submit a transaction
					$success = true;
				} else {
					$ret->reason = "Invalid nonce";
				}
			} else {
				if ($delta < minerSubmitMinSeconds ( $arr ["wallet_id"] )) {
					$ret->reason = "Submitted too soon (" . number_format ( $delta, 2 ) . "<" . minerSubmitMinSeconds ( $arr ["wallet_id"] ) . ")";
				}
				if ($delta > minerSubmitMaxSeconds ( $arr ["wallet_id"] )) {
					$ret->reason = "Submitted too late (" . number_format ( $delta, 2 ) . ">" . minerSubmitMaxSeconds ( $arr ["wallet_id"] ) . ")";
				}
			}
		} else {
			$ret->reason = "Database failure (d)";
		}
	} else {
		$ret->reason = "Unknown job";
	}
} else {
	$ret->reason = "Invalid request";
}

if (! $success) {
	global $api_failure_delay;
	sleep ( $api_failure_delay );
}

endJsonResponse ( $response, $ret, $success, $message );
?>