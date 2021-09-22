<?php
include_once (dirname ( __FILE__ ) . "/../functions.php");

// TODO: re-enable these after fixing them
UserStore::tidyUp ();
UserStore::requestRevalidations ();

InfoStore::getInstance ()->setInfo ( cronHourDebugInfoKey (), "Completed: " . timestampFormat ( timestampNow (), "Y/m/d H:i:s" ) );

echo "Hourly Housekeeing complete\n";
?>
