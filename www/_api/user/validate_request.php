<?php
$ret = startJsonResponse ();

// Called from the website by the user requesting a revalidation - providing username/password as well as recaptcha in $_POST 

echo "ARGS:\n";
print_r ( $args );
echo "_POST[]:\n";
print_r ( $_POST );

$token = $_POST ['token'];
$action = $_POST ['action'];

$success = false;
$message = "User cannot be created\n";
// use the reCAPTCHA PHP client library for validation
$recaptcha = new ReCaptcha\ReCaptcha ( getRecaptchaSecretKey () );
$resp = $recaptcha->setExpectedAction ( $action )->setScoreThreshold ( 0.5 )->verify ( $token, $_SERVER ['REMOTE_ADDR'] );

// verify the response
if ($resp->isSuccess ()) {
	echo "Loading data into user array\n";
	$store = new UserStore ();
	$user = $store->authenticate ( @$_POST ["email"], @$_POST ["password"] );
	$success = is_array ( $user );
	if ($success) {
		$ret->challenge = $store->revalidateUser ( $user ["email"] );
		$message = "Validation request setup\n";
	} else {
		$message = "Unable to find user\n";
	}
	// $ret->words = $cwords;
} else {
	echo "Google says no:\n";
	print_r ( $resp->getErrorCodes () );
	sleep ( 3 );
}

endJsonResponse ( $response, $ret, $success, $message );
?>