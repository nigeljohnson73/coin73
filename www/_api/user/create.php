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

	// Move this into validate account and pass challenge back.
	echo "Creating word list for poormans MFA\n";
	$words = array ();
	$words [] = "Wedensday";
	$words [] = "Helper";
	$words [] = "Utility";
	$words [] = "Layout";
	$words [] = "Floating";
	$words [] = "Efficient";
	$words [] = "Miner";
	$words [] = "Apple";
	$words [] = "Verify";
	$words [] = "Brand";
	$words [] = "Power";
	$words [] = "Private";
	$words [] = "About";
	$words [] = "Document";
	$words [] = "Manual";
	$words [] = "Server";
	$words [] = "Home";
	$words [] = "Arrow";
	$words [] = "Keyboard";
	$words [] = "Words";
	$words [] = "Change";
	$words [] = "Number";
	$words [] = "Letter";
	$words [] = "Reduce";
	$words [] = "Website";
	$words [] = "Printer";
	$words [] = "Flask";
	$words [] = "Reverse";
	$words [] = "Change";
	$words [] = "Value";
	$words [] = "Slice";
	$words [] = "Email";
	$words [] = "Pencil";
	$words [] = "Ruler";

	$keys = array_rand ( $words, 3 );
	$challenge = $words [$keys [0]];
	foreach ( $keys as $key ) {
		$cwords [] = $words [$key];
	}
	shuffle ( $cwords );
	// echo "Chosen '".$challenge."' from:\n";
	// print_r($cwords);

	$validation = new StdClass ();
	$validation->expect = $challenge;
	$validation->choices = $cwords;
	$user ["validation"] = json_encode ( $validation );
	print_r ( $validation );

	$user = $store->insert ( $user );
	$success = is_array ( $user );
	if ($success) {
		ksort ( $user );
		echo "Created user: \n";
		print_r ( $user );
		$message = "User can be created (but wasn't)\n";
	} else {
		$message = "User insert failed\n";
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