<?php
// Called from the website by the user requesting a revalidation - providing username/password as well as recaptcha details in $_POST
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
if (isset ( $_POST ["token"] ) && isset ( $_POST ["action"] ) && isset ( $_POST ["email"] ) && isset ( $_POST ["password"] )) {
	// use the reCAPTCHA PHP client library for validation
	$recaptcha = new ReCaptcha\ReCaptcha ( getRecaptchaSecretKey () );
	$resp = $recaptcha->setExpectedAction ( $_POST ['action'] )->setScoreThreshold ( 0.5 )->verify ( $_POST ['token'], $_SERVER ['REMOTE_ADDR'] );

	if ($resp->isSuccess ()) {
		$store = UserStore::getInstance ();
		$user = $store->authenticate ( @$_POST ["email"], @$_POST ["password"] );
		if (is_array ( $user )) {
			$ret->challenge = $store->revalidateUser ( $user ["email"] );
			$success = strlen ( $ret->challenge );
			if ($success) {
				if ($user ["validation_data"]) {
					$ret->warning = "There is an outstanding validation request. If you did not receive the email, please check your spam folder, and only follow the link in the latest email. If you did not request these, you may want to <a href='/recover'>recover your account.";
				}
					
				$message = "Validation request setup\n";
			} else {
				$message = "Validation request setup failed\n";
				$ret->reason = "The request setup failed - the multifactor process did not complete";
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
	$ret->reason = "The validation request data was invalid - seek an administrator";
}

if (! $success) {
	global $api_failure_delay;
	sleep ( $api_failure_delay );
}
endJsonResponse ( $response, $ret, $success, $message );
?>