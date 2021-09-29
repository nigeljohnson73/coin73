<?php
include_once (dirname ( __FILE__ ) . "/../functions.php");

if (InfoStore::get ( switchKeyBlockCreation (), switchEnabled () ) == "RESETTING") {
	exit ();
}

// TBD

InfoStore::set ( cronDayDebugInfoKey (), "Completed: " . timestampFormat ( timestampNow (), "Y/m/d H:i:s" ) );

echo "Daily Housekeeing complete\n";
?>
