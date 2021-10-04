<?php
include_once (dirname ( __FILE__ ) . "/../functions.php");

if (!InfoStore::cronEnabled()) {
	exit();
}

if (InfoStore::get ( switchKeyBlockCreation (), switchEnabled () ) == "RESETTING") {
	exit ();
}

// TODO: re-enable these after fixing them
UserStoreHouseKeeper::tidyUp ();
UserStoreHouseKeeper::requestRevalidations ();

InfoStore::set ( cronHourDebugInfoKey (), "Completed: " . timestampFormat ( timestampNow (), "Y/m/d H:i:s" ) );

echo "Hourly Housekeeing complete\n";
?>
