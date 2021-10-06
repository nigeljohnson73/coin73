<?php
// Called from the website by the user requesting a rerecovery - providing username/password as well as recaptcha details in $_POST
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
if (isset ( $_POST ["token"] ) && isset ( $_POST ["action"] ) && isset ( $_POST ["email"] )) {
	global $recaptcha_recover_threshold;
	// use the reCAPTCHA PHP client library for recovery
	$recaptcha = new ReCaptcha\ReCaptcha ( getRecaptchaSecretKey () );
	$resp = $recaptcha->setExpectedAction ( $_POST ['action'] )->setScoreThreshold ( $recaptcha_recover_threshold )->verify ( $_POST ['token'], $_SERVER ['REMOTE_ADDR'] );

	if ($resp->isSuccess ()) {
		$user = UserStore::getItemById ( @$_POST ["email"] );
		if (is_array ( $user )) {
			if (! $user ["recovery_nonce"]) {
				$ret->challenge = UserStore::recoverUser ( $user ["email"] );
				$success = strlen ( $ret->challenge );
				if ($success) {
					$message = "Recovery request setup";
				} else {
					$message = "Recovery request setup failed";
					$ret->reason = "The request setup failed - the multifactor process did not complete";
				}
			} else {
				global $token_timeout_hours;
				$message = "Outstanding recovery";
				$ret->reason = "There is an outstanding recovery request. You can wait " . $token_timeout_hours . " hours, or [revalidate your account](/validate) with your existing password.";
			}
		} else {
			// $success = true;
			$message = "Unable to find user";
			$ret->reason = "The request was invalid - your user details could not be found";
		}
	} else {
		logger ( LL_DBG, "Google says no:" );
		logger ( LL_DBG, ob_print_r ( $resp->getErrorCodes () ) );
		$message = "Request is not valid";
		$ret->reason = "The request was invalid - Google did not like the cut of your jib";
	}
} else {
	$message = "Request is not complete";
	$ret->reason = "The recovery request data was invalid - seek an administrator";
}

if (! $success) {
	global $api_failure_delay;
	sleep ( $api_failure_delay );
}
endJsonResponse ( $response, $ret, $success, $message );
?>