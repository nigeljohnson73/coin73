<?php
include_once (dirname ( __FILE__ ) . "/../functions.php");

logger ( LL_INF, "Cron::h1(): Starting" );
if (! InfoStore::cronEnabled ()) {
	logger ( LL_INF, "Cron::h1(): Disabled by switch" );
	exit ();
}

if (InfoStore::get ( switchKeyBlockCreation (), switchEnabled () ) == "RESETTING") {
	logger ( LL_INF, "Cron::h1(): Disabled by blockchain reset" );
	exit ();
}

// TODO: re-enable these after fixing them
logger ( LL_INF, "Cron::h1(): User housekeeping" );
UserStoreHouseKeeper::tidyUp ();
UserStoreHouseKeeper::requestRevalidations ();

InfoStore::set ( cronHourDebugInfoKey (), "Completed: " . timestampFormat ( timestampNow (), "Y/m/d H:i:s" ) );
logger ( LL_INF, "Cron::h1(): Complete" );
?>
