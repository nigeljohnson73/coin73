<?php
// Called from the website by the user requesting a revalidation - providing username/password as well as recaptcha details in $_POST
//
$ret = startJsonResponse ();

echo "ARGS:\n";
print_r ( $args );
echo "_POST[]:\n";
print_r ( @$_POST );

$success = false;
$message = ""; // Used by the toaster pop-up
$ret->reason = ""; // Used in the page alerts

// verify the response
if (isset ( $_POST ["token"] ) && isset ( $_POST ["action"] ) && isset ( $_POST ["email"] ) && isset ( $_POST ["password"] )) {
	// use the reCAPTCHA PHP client library for validation
	$recaptcha = new ReCaptcha\ReCaptcha ( getRecaptchaSecretKey () );
	$resp = $recaptcha->setExpectedAction ( $_POST ['action'] )->setScoreThreshold ( 0.5 )->verify ( $_POST ['token'], $_SERVER ['REMOTE_ADDR'] );

	if ($resp->isSuccess ()) {
		$store = new UserStore ();
		$user = $store->authenticate ( @$_POST ["email"], @$_POST ["password"] );
		if (is_array ( $user )) {
			$ret->challenge = $store->revalidateUser ( $user ["email"] );
			$success = strlen ( $ret->challenge );
			if ($success) {
				$message = "Validation request setup\n";
			} else {
				$message = "Validation request setup failed\n";
				$ret->reason = "The request setup failed - The Multifactor process could not complete";
			}
		} else {
			$message = "Unable to find user\n";
			$ret->reason = "The request was invalid - your user details could not be authenticated";
		}
	} else {
		echo "Google says no:\n";
		print_r ( $resp->getErrorCodes () );
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