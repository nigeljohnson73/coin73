<?php
// This api is called when the the user has requested a user creation providing username/password, toc approval as well as recaptcha details in $_POST
//
session_id ( getDataNamespace () );
session_start ();
$ret = startJsonResponse ();

logger ( LL_DBG, "ARGS:" );
logger ( LL_DBG, ob_print_r ( $args ) );
logger ( LL_DBG, "_POST[]:" );
logger ( LL_DBG, ob_print_r ( $_POST ) );
logger ( LL_DBG, "_SESSION[]:" );
logger ( LL_DBG, ob_print_r ( $_SESSION ) );

$success = false;
$message = "";
$ret->disabled = false;

if (isset ( $_POST ["token"] ) && isset ( $_POST ["action"] ) && isset ( $_POST ["email"] ) && isset ( $_POST ["password"] ) && isset ( $_POST ["accept_toc"] )) {
	global $valid_password_regex;
	if (! $_POST ["accept_toc"]) {
		$message = "User login failed";
		$ret->reason = "Not sure how it happened, but you still have to accept the terms of use";
	} else if (filter_var ( $_POST ["email"], FILTER_VALIDATE_EMAIL ) === false) {
		$message = "User login failed";
		$ret->reason = "Not sure how it happened, but the email address you provided seems to be invalid";
	} else if (preg_match ( "/" . $valid_password_regex . "/", $_POST ["password"] ) === false) {
		$message = "User login failed";
		$ret->reason = "Not sure how it happened, but the password you provided seems to be in an invalid format";
	} else {

		if (InfoStore::loginEnabled ()) {
			// use the reCAPTCHA PHP client library for validation
			$recaptcha = new ReCaptcha\ReCaptcha ( getRecaptchaSecretKey () );
			$resp = $recaptcha->setExpectedAction ( $_POST ["action"] )->setScoreThreshold ( 0.5 )->verify ( $_POST ["token"], $_SERVER ['REMOTE_ADDR'] );

			if ($resp->isSuccess ()) {
				$store = UserStore::getInstance ();
				$user = $store->authenticate ( @$_POST ["email"], @$_POST ["password"] );
				if (is_array ( $user )) {
					// if (strlen ( $user ["recovery_data"] )) {
					// $ret->reason = "There is an outstanding recovery request. Please complete that first.";
					// } else
					if (strlen ( $user ["validation_data"] ) == 0) {
						if (! $user ["locked"]) {
							$user ["logged_in"] = timestampNow ();
							$store->update ( $user );
							$success = true;
							$message = "User authenticated\n";
							$_SESSION ["AUTHTOK"] = $user ["guid"];
							$ret->user = sanitiseUser ( $user );
						} else {
							$ret->reason = "This account is locked.";
						}
					} else {
						$ret->reason = "There is an outstanding validation request. Please complete that first.";
					}
				} else {
					$message = "Unable to find user\n";
					$ret->reason = "The request was invalid - your user details could not be authenticated";
				}
			} else {
				logger ( LL_DBG, "Google says no:" );
				logger ( LL_DBG, ob_print_r ( $resp->getErrorCodes () ) );
				$ret->reason = "The request was invalid - Google did not like the cut of your jib";
			}
		} else {
			$ret->reason = "Logins are currently disabled";
			$ret->disabled = true;
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
