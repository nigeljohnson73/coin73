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

if (isset ( $_SESSION ["AUTHTOK"] ) && isset ( $_POST ["token"] ) && isset ( $_POST ["action"] ) && isset ( $_POST ["recipient"] ) && isset ( $_POST ["amount"] )) {
	if (strlen ( $_POST ["recipient"] ) != 130) {
		$message = "Transaction failed";
		$ret->reason = "Recipient address is invalid";
	} else if (doubleval ( $_POST ["amount"] ) <= 0) {
		$message = "Transaction failed";
		$ret->reason = "Amount is invalid";
	} else {

		if (InfoStore::transactionsEnabled ()) {
			// use the reCAPTCHA PHP client library for validation
			$recaptcha = new ReCaptcha\ReCaptcha ( getRecaptchaSecretKey () );
			$resp = $recaptcha->setExpectedAction ( $_POST ["action"] )->setScoreThreshold ( 0.5 )->verify ( $_POST ["token"], $_SERVER ['REMOTE_ADDR'] );

			// verify the response
			if ($resp->isSuccess ()) {
				$sender = UserStore::getInstance ()->getItemByGuid ( $_SESSION ["AUTHTOK"] );
				if ($sender) {
					$k = KeyStore::getInstance()->getKeys($sender["email"]);
					print_r($k);
					if($k && $k->private) {
					// if ($_POST ["amount"] <= $sender ["balance"]) {
					$t = new Transaction ( $sender ["public_key"], $_POST ["recipient"], $_POST ["amount"], $_POST ["message"] ?? "");
					if($t->sign($k->private)) {
						if(TransactionStore::getInstance()->addTransaction($t)) {
							//$ret->reason = "It worked";
							$ret->success = true;
						} else {
							$ret->reason = TransactionStore::getInstance()->getReason();
						}
					} else {
						$ret->reason = "Unable to sign transaction";
					}
					// } else {
					// }
					} else {
						$ret->reason = "Sender keypair is not valid";
					}
				} else {
					$ret->reason = "The request was invalid - Sender could not be found";
				}
			} else {
				echo "Google says no:\n";
				print_r ( $resp->getErrorCodes () );
				$ret->reason = "The request was invalid - Google did not like the cut of your jib";
			}
		} else {
			$ret->reason = "Transactions are currently disbled";
		}
	}
} else {
	$message = "Request is not complete\n";
	$ret->reason = "The transaction request data was invalid - seek an administrator";
}

if (! $success) {
global $api_failure_delay;
sleep ( $api_failure_delay );
}

endJsonResponse ( $response, $ret, $success, $message );
?>