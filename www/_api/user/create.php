<?php
$ret = startJsonResponse ();

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
	$user = array ();
	$store = new UserStore ();
	$fields = $store->getDataFields ();
	$fields [] = $store->getKeyField ();
	foreach ( $fields as $k ) {
		if (isset ( $_POST [$k] )) {
			$user [$k] = $_POST [$k];
		}
	}

	$user = $store->insert ( $user );
	$success = is_array ( $user );
	if ($success) {
		$challenge = $store->revalidateUser ( $user ["email"] );
		ksort ( $user );
		echo "Created user: \n";
		print_r ( $user );
		$message = "User successfully created\n";
	} else {
		$message = "User creation failed\n";
	}
	$ret->challenge = $challenge;
	// $ret->words = $cwords;
} else {
	echo "Google says:\n";
	print_r ( $resp->getErrorCodes () );
}
sleep ( 3 );

endJsonResponse ( $response, $ret, $success, $message );
?>