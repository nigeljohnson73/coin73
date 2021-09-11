<?php
// This api is called when the recovery page is called with a payload in args, i.e. a user has clicked a link in the recovery email. No $_POST variables are expected
//
$ret = startJsonResponse ();

echo "ARGS:\n";
print_r ( $args );
echo "_POST[]:\n";
print_r ( $_POST );

$ret->reason = "";
$success = false;
$message = "Unable to prepare recovery";

$store = new UserStore ();
if (isset ( $_POST ["payload"] )) {
	$user = $store->getItemByRecoveryNonce ( $_POST ["payload"] );
	if ($user) {
		// Invalidate this request
		$user ["recovery_nonce"] = "";
		$user = $store->replace ( $user );

		if (is_array ( $user )) {
			global $token_timeout_hours;

			$requested = $user ["recovery_requested"];
			$recovery_expiry_seconds = $token_timeout_hours * 60 * 60; // 24 hours;

			if ((timestamp2Time ( timestampNow () ) - timestamp2Time ( $requested )) < $recovery_expiry_seconds) {
				$ret->choices = json_decode ( $user ["recovery_data"] )->choices;
				$ret->guid = $user ["guid"];
				$success = true;
				$message = "";
			} else {
				echo "Token has expired\n";
				echo "    Token timeout hours: " . $token_timeout_hours . "\n";
				echo "    Time created: " . timestampFormat ( $requested, "Y/m/d H:i:s" ) . " (" . timestamp2Time ( $requested ) . ")\n";
				echo "    Time now: " . timestampFormat ( timestampNow (), "Y/m/d H:i:s" ) . " (" . timestamp2Time ( timestampNow () ) . ")\n";
				echo "    Creation Delta: " . (timestamp2Time ( timestampNow () ) - timestamp2Time ( $requested )) . " seconds\m";
				echo "    Token timeout: " . $recovery_expiry_seconds . " seconds\n";
				$ret->reason = "Validation request has expired. Please <a href='/validate'>start the rerecovery process</a> again.";
			}
			print_r ( $user );
		} else {
			echo "Unable to update user details\n";
			$ret->reason = "Validation preparation failed - Unable to update user details.";
		}
	} else {
		echo "Unable to find nonce\n";
		$ret->reason = "Validation request is not valid (Maybe you have used the email link already). Please <a href='/validate'>start the recovery process</a> again.";
	}
} else {
	echo "no payload sent - bad bot!!!\n";
	$ret->reason = "Validation data cannot be identified.";
}

if (! $success) {
	global $api_failure_delay;
	sleep ( $api_failure_delay );
}
endJsonResponse ( $response, $ret, $success, $message );
?>