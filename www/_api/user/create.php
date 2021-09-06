<?php
$ret = startJsonResponse ();

echo "ARGS:\n";
print_r ( $args );
echo "_POST[]:\n";
print_r ( $_POST );

$email = $_POST ["email"];
$password = $_POST ["password"];
$token = $_POST ['token'];
$action = $_POST ['action'];

$success = false;
$message = "User cannot be created\n";
// use the reCAPTCHA PHP client library for validation
$recaptcha = new ReCaptcha\ReCaptcha ( getRecaptchaSecretKey () );
$resp = $recaptcha->setExpectedAction ( $action )->setScoreThreshold ( 0.5 )->verify ( $token, $_SERVER ['REMOTE_ADDR'] );

// verify the response
if ($resp->isSuccess ()) {
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

	$success = true;
	$message = "User can be created (but wasn't)\n";
	$ret->challenge = $challenge;
	// $ret->words = $cwords;
} else {
	echo "Google says:\n";
	print_r ( $resp->getErrorCodes () );
}
sleep ( 3 );
endJsonResponse ( $response, $ret, $success, $message );
?>