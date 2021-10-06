<?php
// This api is called when the the user has requested a user creation providing username/password, toc approval as well as recaptcha details in $_POST
//
$ret = startJsonResponse ();

logger ( LL_DBG, "ARGS:" );
logger ( LL_DBG, ob_print_r ( $args ) );
logger ( LL_DBG, "_POST[]:" );
logger ( LL_DBG, ob_print_r ( $_POST ) );

$success = false;
$message = "";

// $ret->reason = "The world ended";

// $success = true;
// $ret->challenge = "Wibble";

if (isset ( $_POST ["token"] ) && isset ( $_POST ["action"] ) && isset ( $_POST ["email"] ) && isset ( $_POST ["password"] ) && isset ( $_POST ["accept_toc"] )) {
	global $recaptcha_create_threshold;
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

		if (InfoStore::signupEnabled ()) {
			// use the reCAPTCHA PHP client library for validation
			$recaptcha = new ReCaptcha\ReCaptcha ( getRecaptchaSecretKey () );
			$resp = $recaptcha->setExpectedAction ( $_POST ["action"] )->setScoreThreshold ( $recaptcha_create_threshold )->verify ( $_POST ["token"], $_SERVER ['REMOTE_ADDR'] );

			// verify the response
			if ($resp->isSuccess ()) {
				logger ( LL_DBG, "Loading data into user array" );
				$user = array ();
				$fields = UserStore::getDataFields ();
				$fields [] = UserStore::getKeyField ();
				foreach ( $fields as $k ) {
					if (isset ( $_POST [$k] )) {
						$user [$k] = $_POST [$k];
					}
				}

				$user = UserStore::insert ( $user );
				if (is_array ( $user )) {
					$ret->challenge = UserStore::revalidateUser ( $user ["email"] );
					ksort ( $user );
					echo "Created user: \n";
					print_r ( $user );
					$success = strlen ( $ret->challenge );
					if ($success) {
						$message = "User created successfuly\n";
					} else {
						$message = "User creation failed\n";
						$ret->reason = "The user setup failed - The Multifactor process could not complete";
					}
				} else {
					$message = "User creation failed\n";
					$ret->reason = "The user setup failed - Looks like a database issue";
				}
				// $ret->words = $cwords;
			} else {
				echo "Google says no:\n";
				print_r ( $resp->getErrorCodes () );
				$ret->reason = "The request was invalid - Google did not like the cut of your jib";
			}
		} else {
			$ret->reason = "Signups are currently disbled";
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