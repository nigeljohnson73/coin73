<?php
// This api is called when the the user has requested a user creation providing username/password, toc approval as well as recaptcha details in $_POST
//
session_start ();
$ret = startJsonResponse ();

echo "ARGS:\n";
print_r ( $args );
echo "_POST[]:\n";
print_r ( $_POST );

$success = true;
$message = "";

session_destroy();

if (! $success) {
	global $api_failure_delay;
	sleep ( $api_failure_delay );
}

endJsonResponse ( $response, $ret, $success, $message );
?>