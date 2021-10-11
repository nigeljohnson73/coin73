<?php
// This api is called when the the user has requested a user creation providing username/password, toc approval as well as recaptcha details in $_POST
//
// session_id ( getDataNamespace () );
// session_start ();
$ret = startJsonResponse();

logger(LL_DBG, "ARGS:");
logger(LL_DBG, ob_print_r($args));
logger(LL_DBG, "_POST[]:");
logger(LL_DBG, ob_print_r($_POST));
logger(LL_DBG, "_SESSION[]:");
logger(LL_DBG, ob_print_r($_SESSION));

$success = false;
$message = "";
$ret->disabled = false;

if (InfoStore::loginEnabled()) {
	if (isset($_SESSION["AUTHTOK"])) {
		// $store = UserStore::getInstance ();
		$user = UserStore::getItemByGuid($_SESSION["AUTHTOK"]);
		if (is_array($user)) {
			if (strlen($user["validation_data"]) == 0) {
				if (!$user["locked"]) {
					$user["logged_in"] = timestampNow();
					UserStore::update($user);
					$success = true;
					// $message = "User authenticated\n";
					$_SESSION["AUTHTOK"] = $user["guid"];
					$ret->user = sanitiseUser($user);
				} else {
					$ret->reason = "This account is locked.";
				}
			} else {
				$ret->reason = "There is an outstanding validation request. Please complete that first.";
			}
		} else {
			global $api_failure_delay;
			sleep($api_failure_delay);
		}
	}
} else {
	$ret->reason = "Logins are currently disabled";
	$ret->disabled = true;
}

// if (! $success) {
// global $api_failure_delay;
// sleep ( $api_failure_delay );
// }

endJsonResponse($response, $ret, $success, $message);
