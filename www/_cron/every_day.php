<?php
include_once (dirname ( __FILE__ ) . "/../functions.php");
// Check for header: "X-Appengine-Cron: true"

// TBD

InfoStore::getInstance ()->setInfo ( cronDayDebugInfoKey (), "Completed: " . timestampFormat ( timestampNow (), "Y/m/d H:i:s" ) );

echo "Daily Housekeeing complete\n";
?>
