<?php
// This api is called when the validation page is called with a payload in args, i.e. a user has clicked a link in the validation email. No $_POST variables are expected
//
$ret = startJsonResponse ();

logger ( LL_DBG, "ARGS:" );
logger ( LL_DBG, ob_print_r ( $args ) );
logger ( LL_DBG, "_POST[]:" );
logger ( LL_DBG, ob_print_r ( $_POST ) );

$ret->reason = "";
$success = false;
$message = "Unable to prepare validation";

$store = new UserStore ();
if (isset ( $_POST ["payload"] )) {
	$user = $store->getItemByValidationNonce ( $_POST ["payload"] );
	if ($user) {
		// Invalidate this request
		$user ["validation_nonce"] = "";
		$user = $store->replace ( $user );

		if (is_array ( $user )) {
			global $token_timeout_hours;

			$requested = $user ["validation_requested"];
			$expiry_seconds = $token_timeout_hours * 60 * 60; // 24 hours;

			if ((timestamp2Time ( timestampNow () ) - timestamp2Time ( $requested )) < $expiry_seconds) {
				$ret->choices = json_decode ( $user ["validation_data"] )->choices;
				$ret->guid = $user ["guid"];
				$success = true;
				$message = "";
			} else {
				logger ( LL_DBG, "Token has expired" );
				logger ( LL_DBG, "    Token timeout hours: " . $token_timeout_hours );
				logger ( LL_DBG, "    Time created: " . timestampFormat ( $requested, "Y/m/d H:i:s" ) . " (" . timestamp2Time ( $requested ) . ")" );
				logger ( LL_DBG, "    Time now: " . timestampFormat ( timestampNow (), "Y/m/d H:i:s" ) . " (" . timestamp2Time ( timestampNow () ) . ")" );
				logger ( LL_DBG, "    Creation Delta: " . (timestamp2Time ( timestampNow () ) - timestamp2Time ( $requested )) . " seconds" );
				logger ( LL_DBG, "    Token timeout: " . $expiry_seconds . " seconds" );
				$ret->reason = "Validation request has expired. Please <a href='/validate'>start the rerecovery process</a> again.";
			}
			print_r ( $user );
		} else {
			logger ( LL_DBG, "Unable to update user details" );
			$ret->reason = "Validation preparation failed - Unable to update user details.";
		}
	} else {
		logger ( LL_DBG, "Unable to find nonce" );
		$ret->reason = "Validation request is not valid (Maybe you have used the email link already). Please <a href='/validate'>start the recovery process</a> again.";
	}
} else {
	logger ( LL_DBG, "no payload sent - bad bot!!!" );
	$ret->reason = "Validation data cannot be identified.";
}

$ret->warning = "Hello World. <a href='/'>Go Home</a>";
if (! $success) {
	global $api_failure_delay;
	sleep ( $api_failure_delay );
}
endJsonResponse ( $response, $ret, $success, $message );
?>