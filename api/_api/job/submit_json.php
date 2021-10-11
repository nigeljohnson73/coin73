<?php
// This api is called when the the user has requested a user creation providing username/password, toc approval as well as recaptcha details in $_POST
//
$ret = startJsonResponse();

logger(LL_DBG, "ARGS:");
logger(LL_DBG, ob_print_r($args));
logger(LL_DBG, "_POST[]:");
logger(LL_DBG, ob_print_r($_POST));

$success = false;
$message = "";

$ret->data = new StdClass();

if (isset($args["job_id"]) && isset($args["nonce"])) {
	$hashrate = isset($_POST["hashrate"]) ? $_POST["hashrate"] : 0;
	$chiptype = isset($_POST["chiptype"]) ? $_POST["chiptype"] : "";

	if (InfoStore::miningEnabled()) {
		// $store = JobStore::getInstance ();

		$arr = JobStore::getItemById($args["job_id"]);
		if (is_array($arr)) {
			$arr = JobStore::getInstance()->delete($arr);
			if (is_array($arr)) {
				$delta = (msTime() - $arr["created"]);
				if ($delta >= minerSubmitMinSeconds($arr["wallet_id"]) && ($delta <= minerSubmitMaxSeconds($arr["wallet_id"]))) {

					$hash = hash("sha1", $arr["hash"] . $args["nonce"]);
					$starts = str_pad("", minerDifficulty(), "0");
					if (strpos($hash, $starts) === 0) {
						$expected_seconds = minerSubmitTargetSeconds($arr["wallet_id"]);
						$seconds_per_day = 60 * 60 * 24;
						$slots_per_day = $seconds_per_day / $expected_seconds;

						$coin_per_day = minerRewardTargetPerDay($arr["wallet_id"]);
						$coin_per_slot = $coin_per_day / $slots_per_day;
						logger(LL_DBG, "--------");
						logger(LL_DBG, "Seconds per day: " . number_format($seconds_per_day, 2));
						logger(LL_DBG, "Slots per day: " . number_format($slots_per_day, 2));
						logger(LL_DBG, "Coins per day: " . number_format($coin_per_day, 2));
						logger(LL_DBG, "Coins per slot: " . number_format($coin_per_slot, 6));
						logger(LL_DBG, "--------");

						$effective_miners = effectiveMinerEfficiency($arr["shares"]);
						$miner_efficiency = $effective_miners / $arr["shares"];
						logger(LL_DBG, "Physical miners: " . number_format($arr["shares"], 2));
						logger(LL_DBG, "Effective miners: " . number_format($effective_miners, 2));
						logger(LL_DBG, "Mining efficiency: " . number_format(100 * $miner_efficiency, 2));
						logger(LL_DBG, "--------");

						$sub_time_pcnt = submissionReward($delta, $arr["wallet_id"]);
						logger(LL_DBG, "Submission time: " . number_format(100 * $sub_time_pcnt, 2) . "%");
						logger(LL_DBG, "--------");
						$coin = $coin_per_slot * $sub_time_pcnt * $miner_efficiency;
						logger(LL_DBG, "Transaction amount: " . number_format($coin, 6));
						logger(LL_DBG, "--------");
						logger(LL_XDBG, "setting transaction label: '" . minerRewardLabel() . " " . $arr["rig_id"] . "'");
						logger(LL_XDBG, "--------");
						$t = new Transaction(coinbaseWalletId(), $arr["wallet_id"], $coin, minerRewardLabel() . " " . $arr["rig_id"]);
						if ($t->sign(coinbasePrivateKey())) {
							// Bypass all the checked here, but this is trusted teratory
							if (TransactionStore::getInstance()->insert($t->unload())) {
								$success = true;
							} else {
								$ret->reason = "Transaction submit failed";
							}
						} else {
							$ret->reason = "Transaction signing failed";
						}
					} else {
						$ret->reason = "Invalid nonce";
					}
				} else {
					if ($delta < minerSubmitMinSeconds($arr["wallet_id"])) {
						if ($delta < (minerSubmitMinSeconds($arr["wallet_id"]) - 0.5)) {
							$ret->reason = "Submitted waaaaaay too soon (" . number_format($delta, 2) . "<" . minerSubmitMinSeconds($arr["wallet_id"]) . ")";
							sleep(minerSubmitPunishment());
						} else {
							$ret->reason = "Submitted too soon (" . number_format($delta, 2) . "<" . minerSubmitMinSeconds($arr["wallet_id"]) . ")";
						}
					}
					if ($delta > minerSubmitMaxSeconds($arr["wallet_id"])) {
						$ret->reason = "Submitted too late (" . number_format($delta, 2) . ">" . minerSubmitMaxSeconds($arr["wallet_id"]) . ")";
					}
				}
			} else {
				$ret->reason = "Database failure (d)";
			}
		} else {
			$ret->reason = "Unknown job";
		}
	} else {
		$ret->reason = "Mining currenty disabled";
	}
} else {
	$ret->reason = "Invalid request";
}

if (!$success) {
	global $api_failure_delay;
	sleep($api_failure_delay);
}

endJsonResponse($response, $ret, $success, $message);
