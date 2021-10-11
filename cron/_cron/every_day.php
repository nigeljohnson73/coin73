<?php
include_once(dirname(__FILE__) . "/../functions.php");

logger(LL_INF, "Cron::d1(): Starting");
if (!InfoStore::cronEnabled()) {
	logger(LL_INF, "Cron::d1(): Disabled by switch");
	exit();
}

if (InfoStore::get(switchKeyBlockCreation(), switchEnabled()) == "RESETTING") {
	logger(LL_INF, "Cron::d1(): Disabled by blockchain reset");
	exit();
}

// TBD

// Reset the blockchain overnight
logger(LL_INF, "Cron::d1(): Enabling blockchain reset");
InfoStore::set("switch_reset_blockchain", switchEnabled());

InfoStore::set(cronDayDebugInfoKey(), "Completed: " . timestampFormat(timestampNow(), "Y/m/d H:i:s"));
logger(LL_INF, "Cron::d1(): Complete");
