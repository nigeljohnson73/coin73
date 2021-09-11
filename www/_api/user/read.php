<?php
// This api is called when the the user has requested a user creation providing username/password, toc approval as well as recaptcha details in $_POST
//
session_start ();
$ret = startJsonResponse ();

echo "ARGS:\n";
print_r ( $args );
echo "_POST[]:\n";
print_r ( $_POST );
echo "_SESSION[]:\n";
print_r ( $_SESSION );

$success = false;
$message = "";

function sanitiseUser($user) {
	$ret = new StdClass ();
	echo "Sanitise user:\n";
	print_r ( $user );

	$ret->public_key = @$user ["public_key"];
	$ret->balance = @$user ["balance"];

	print_r ( $ret );
	return $ret;
}

if (isset ( $_SESSION ["AUTHTOK"] )) {
	$store = new UserStore ();
	$user = $store->getItemByGuid ( $_SESSION ["AUTHTOK"] );
	if (is_array ( $user )) {
		$ret->user = sanitiseUser ( $user );
		$success = true;
	} else {
		global $api_failure_delay;
		sleep ( $api_failure_delay );
	}
}

// if (! $success) {
// global $api_failure_delay;
// sleep ( $api_failure_delay );
// }

endJsonResponse ( $response, $ret, $success, $message );
?>