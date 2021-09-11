<?php
// This api is called when the the user has requested a user creation providing username/password, toc approval as well as recaptcha details in $_POST
//
session_start ();
$ret = startJsonResponse ();

echo "ARGS:\n";
print_r ( $args );
echo "_POST[]:\n";
print_r ( $_POST );
echo "_COOKIE[]:\n";
print_r ( _COOKIE );

$success = false;
$message = "";

if (isset ( $_POST ["token"] ) && isset ( $_POST ["action"] ) && isset ( $_POST ["email"] ) && isset ( $_POST ["password"] ) && isset ( $_POST ["accept_toc"] )) {
	global $valid_password_regex;
	if (! $_POST ["accept_toc"]) {
		$message = "User creation failed";
		$ret->reason = "Not sure how it happened, but you still have to accept the terms of use";
	} else if (filter_var ( $_POST ["email"], FILTER_VALIDATE_EMAIL ) === false) {
		$message = "User creation failed";
		$ret->reason = "Not sure how it happened, but the email address you provided seems to be invalid";
	} else if (preg_match ( "/" . $valid_password_regex . "/", $_POST ["password"] ) === false) {
		$message = "User creation failed";
		$ret->reason = "Not sure how it happened, but the password you provided seems to be invalid";
	} else {

		// use the reCAPTCHA PHP client library for validation
		$recaptcha = new ReCaptcha\ReCaptcha ( getRecaptchaSecretKey () );
		$resp = $recaptcha->setExpectedAction ( $_POST ["action"] )->setScoreThreshold ( 0.5 )->verify ( $_POST ["token"], $_SERVER ['REMOTE_ADDR'] );

		if ($resp->isSuccess ()) {
			$store = new UserStore ();
			$user = $store->authenticate ( @$_POST ["email"], @$_POST ["password"] );
			if (is_array ( $user )) {
				$message = "User authenticated\n";
				$_SESSION ["AUTHTOK"] = $user ["guid"];
				$ret->user = $user;
			} else {
				$message = "Unable to find user\n";
				$ret->reason = "The request was invalid - your user details could not be authenticated";
			}
		} else {
			echo "Google says no:\n";
			print_r ( $resp->getErrorCodes () );
			$ret->reason = "The request was invalid - Google did not like the cut of your jib";
		}
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