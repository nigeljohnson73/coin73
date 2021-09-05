<?php
$ret = startJsonResponse ();

echo "ARGS:\n";
print_r ( $args );
echo "_POST[]:\n";
print_r ( $_POST );


$email = $_POST["email"];
$password = $_POST["password"];
$token = $_POST['token'];
$action = $_POST['action'];

$success = false;
$message = "User cannot be created\n";
// use the reCAPTCHA PHP client library for validation
$recaptcha = new ReCaptcha\ReCaptcha(getRecaptchaSecretKey());
$resp = $recaptcha->setExpectedAction($action)
->setScoreThreshold(0.5)
->verify($token, $_SERVER['REMOTE_ADDR']);

// verify the response
if ($resp->isSuccess()) {
	// valid submission
	// go ahead and do necessary stuff
		$success = true;
		$message = "User can be created (but wasn't)\n";
} else {
	// collect errors and display it
	//$errors = $resp->getErrorCodes();
	echo "Google says:\n";
	print_r($resp->getErrorCodes());
}
sleep(5);
endJsonResponse ( $response, $ret, $success, $message );
?>