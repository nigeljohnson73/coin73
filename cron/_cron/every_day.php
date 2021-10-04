<?php
include_once (dirname ( __FILE__ ) . "/../functions.php");

if (!InfoStore::cronEnabled()) {
	exit();
}

if (InfoStore::get ( switchKeyBlockCreation (), switchEnabled () ) == "RESETTING") {
	exit ();
}

// TBD
// Reset the blockchain overnight
InfoStore::set ( "switch_reset_blockchain", switchEnabled () );

InfoStore::set ( cronDayDebugInfoKey (), "Completed: " . timestampFormat ( timestampNow (), "Y/m/d H:i:s" ) );

echo "Daily Housekeeing complete\n";
?>
