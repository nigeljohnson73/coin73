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

$ret->data = new StdClass ();

function validRigId($str) {
	$matches = [ ];
	preg_match ( "/\A([\w\-]){4,32}\z/", $str, $matches );
	return is_array ( $matches ) && count ( $matches ) && $matches [0] == $str;
}

function validWalletId($str) {
	// Should probably go look it up
	$matches = [ ];
	preg_match ( "/\A([\da-f]){130,130}\z/", $str, $matches );
	return is_array ( $matches ) && count ( $matches ) && $matches [0] == $str;
}

if (isset ( $_POST ["wallet_id"] ) && isset ( $_POST ["rig_id"] )) {
	if (! validWalletId ( $_POST ["wallet_id"] )) {
		$ret->reason = "Invalid wallet_id";
	} else if (! validRigId ( $_POST ["rig_id"] )) {
		$ret->reason = "Invalid rig_id";
	} else {
		$store = new JobStore ();
		$arr = array ();

		$jobs = $store->getItemsByWalletId ( $_POST ["wallet_id"] );
		if (count ( $jobs ) < minerMaxCount ( $_POST ["wallet_id"] )) {
			$miner_in_use = false;
			if (count ( $jobs ) > 0) {
				foreach ( $jobs as $job ) {
					if ($job ["rig_id"] == $_POST ["rig_id"]) {
						$miner_in_use = true;
					}
				}
			}
			if (! $miner_in_use) {
				$arr ["wallet_id"] = $_POST ["wallet_id"];
				$arr ["rig_id"] = $_POST ["rig_id"];
				$arr ["hash"] = hash ( "sha1", $_POST ["wallet_id"] . $_POST ["rig_id"] . timestampNow () . rand () ); // TODO: get the next block hash
				$arr ["difficulty"] = minerDifficulty ();
				$arr ["shares"] = count ( $jobs ) + 1;
				$arr = $store->insert ( $arr );

				if (is_array ( $arr )) {
					$ret->data->job_id = $arr ["job_id"];
					$ret->data->hash = $arr ["hash"];
					$ret->data->difficulty = $arr ["difficulty"];
					$ret->data->target_seconds = minerSubmitTargetSeconds ( $_POST ["wallet_id"] );
					$success = true;
				} else {
					$ret->reason = "Database failure";
				}
			} else {
				$ret->reason = "Miner in use";
			}
			// Get all the jobs for the wallet_id;
		} else {
			$ret->reason = "Miner limit reached";
		}
	}
} else {
	$ret->reason = "Invalid request";
}

if (! $success) {
	global $api_failure_delay;
	sleep ( $api_failure_delay );
}

endJsonResponse ( $response, $ret, $success, $message );
?>