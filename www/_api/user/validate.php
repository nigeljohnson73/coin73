<?php
// Called from the website by the user pressing the MFA button. User GUID and the challenge are expcted in the $_POST as well as the recaptcha
//
$ret = startJsonResponse ();

logger ( LL_DBG, "ARGS:" );
logger ( LL_DBG, ob_print_r ( $args ) );
logger ( LL_DBG, "_POST[]:" );
logger ( LL_DBG, ob_print_r ( $_POST ) );

$success = false;
$message = ""; // Used by the toaster pop-up
$ret->reason = ""; // Used in the page alerts

// verify the response
if (isset ( $_POST ["token"] ) && isset ( $_POST ["action"] ) && isset ( $_POST ["guid"] ) && isset ( $_POST ["challenge"] )) {
	// use the reCAPTCHA PHP client library for validation
	$recaptcha = new ReCaptcha\ReCaptcha ( getRecaptchaSecretKey () );
	$resp = $recaptcha->setExpectedAction ( $_POST ['action'] )->setScoreThreshold ( 0.5 )->verify ( $_POST ['token'], $_SERVER ['REMOTE_ADDR'] );

	if ($resp->isSuccess ()) {
		$store = new UserStore ();
		$user = $store->getItemByGuid ( @$_POST ["guid"] );
		if (is_array ( $user )) {
			$expect = json_decode ( $user ["validation_data"] )->expect;
			$success = $_POST ["challenge"] == $expect;

			$user ["validated"] = timestampNow ();
			$user ["validation_requested"] = 0;
			$user ["validation_reminded"] = 0;
			$user ["validation_nonce"] = "";
			$user ["validation_data"] = "";
			$user = $store->update ( $user );

			if (is_array ( $user )) {
				if ($success) {
					$message = "Validation complete\n";
				} else {
					$message = "Validation failed\n";
					$ret->reason = "The validation failed - that was not the correct challenge. You will need to <a href='/validate'>start again</a>.";
				}
			} else {
				$message = "Update user details failed\n";
				$ret->reason = "The validation failed - unable to save user update";
			}
		} else {
			$message = "Unable to find user\n";
			$ret->reason = "The request was invalid - your user details could not be authenticated";
		}
	} else {
		logger ( LL_DBG, "Google says no:" );
		logger ( LL_DBG, ob_print_r ( $resp->getErrorCodes () ) );
		$message = "Request is not valid";
		$ret->reason = "The request was invalid - Google did not like the cut of your jib";
	}
} else {
	$message = "Request is not complete\n";
	$ret->reason = "The validation data was invalid - seek an administrator";
}

if (! $success) {
	global $api_failure_delay;
	sleep ( $api_failure_delay );
}
endJsonResponse ( $response, $ret, $success, $message );
?>