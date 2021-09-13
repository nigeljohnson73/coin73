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
if (isset ( $_POST ["token"] ) && isset ( $_POST ["action"] ) && isset ( $_POST ["guid"] ) && isset ( $_POST ["challenge"] ) && isset ( $_POST ["password"] )) {
	global $valid_password_regex;
	if (! $_POST ["accept_toc"]) {
		$message = "User recovery failed";
		$ret->reason = "Not sure how it happened, but you still have to accept the terms of use";
	} else if (preg_match ( "/" . $valid_password_regex . "/", $_POST ["password"] ) === false) {
		$message = "User recovery failed";
		$ret->reason = "Not sure how it happened, but the password you provided seems to be invalid";
	} else {
		// use the reCAPTCHA PHP client library for recovery
		$recaptcha = new ReCaptcha\ReCaptcha ( getRecaptchaSecretKey () );
		$resp = $recaptcha->setExpectedAction ( $_POST ['action'] )->setScoreThreshold ( 0.5 )->verify ( $_POST ['token'], $_SERVER ['REMOTE_ADDR'] );

		if ($resp->isSuccess ()) {
			$store = new UserStore ();
			$user = $store->getItemByGuid ( @$_POST ["guid"] );
			if (is_array ( $user )) {
				$expect = json_decode ( $user ["recovery_data"] )->expect;
				$success = $_POST ["challenge"] == $expect;

				// $user ["recovered"] = timestampNow ();
				$user ["locked"] = 0;
				$user ["recovery_requested"] = 0;
				$user ["recovery_nonce"] = "";
				$user ["recovery_data"] = "";
				
				$user = $store->replace ( $user );

				if (is_array ( $user )) {
					$user = $store->setPassword ( $user ["email"], $_POST ["password"] );
					if (is_array ( $user )) {
						if ($success) {
							$message = "Recovery complete";
						} else {
							$message = "Recovery failed";
							$ret->reason = "The recovery failed - that was not the correct challenge. You will need to <a href='/recover'>start again</a>.";
						}
					} else {
						$message = "Update user details failed";
						$ret->reason = "The recovery failed - unable to save password";
					}
				} else {
					$message = "Update user details failed";
					$ret->reason = "The recovery failed - unable to save user update";
				}
			} else {
				$message = "Unable to find user";
				$ret->reason = "The request was invalid - your user details could not be authenticated";
			}
		} else {
			logger ( LL_DBG, "Google says no:" );
			logger ( LL_DBG, ob_print_r ( $resp->getErrorCodes () ) );
			$message = "Request is not valid";
			$ret->reason = "The request was invalid - Google did not like the cut of your jib";
		}
	}
} else {
	$message = "Request is not complete\n";
	$ret->reason = "The recovery data was invalid - seek an administrator";
}

if (! $success) {
	global $api_failure_delay;
	sleep ( $api_failure_delay );
}
endJsonResponse ( $response, $ret, $success, $message );
?>