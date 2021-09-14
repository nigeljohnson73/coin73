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