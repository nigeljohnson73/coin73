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

function isValidWalletId($wallet_id) {
	return strlen ( $wallet_id ) == 130;
}

$store = UserStore::getInstance ();
if (isset ( $args ["wallet_id"] )) {
	if (isValidWalletId ( $args ["wallet_id"] )) {
		if ($args ["wallet_id"] == coinbaseWalletId ()) {
			$success = true;
			$ret->data = new StdClass ();
			$ret->data->wallet_id = $args ["wallet_id"];
			$ret->data->balance = 999.9990001;
		} else {
			$user = $store->getItemByWalletId ( $args ["wallet_id"] );
			if (! $user) {
				$ret->reason = "Wallet does not exist";
				logger ( LL_ERR, "Balance(): " . $ret->reason );
			} else {
				$success = true;
				$ret->data = new StdClass ();
				$ret->data->wallet_id = $args ["wallet_id"];
				$ret->data->balance = (double)$user ["balance"];
			}
		}
	} else {
		$ret->reason = "Wallet ID appears to be invalid";
		logger ( LL_ERR, "Balance(): " . $ret->reason );
	}
}

if ($success == false && strlen ( $ret->reason )) {
	$ret->reason = "Not implemented";
}
endJsonResponse ( $response, $ret, $success, $message );
?>