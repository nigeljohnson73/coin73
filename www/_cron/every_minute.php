<?php
include_once (dirname ( __FILE__ ) . "/../functions.php");
// Check for header: "X-Appengine-Cron: true"

// Remove any hung jobs
// TODO: should probably go into system tick
JobStore::tidyUp ();

// TODO: Move this to the system tick handling so it's called every 10 seconds... once it's tuned
transactionToBlock ();

InfoStore::getInstance ()->setInfo ( cronMinuteDebugInfoKey (), "Completed: " . timestampFormat ( timestampNow (), "Y/m/d H:i:s" ) );

echo "Minutely Housekeeing complete\n";
?>
