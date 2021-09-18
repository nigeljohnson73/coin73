<?php
include_once (dirname ( __FILE__ ) . "/../functions.php");

if (@$_SERVER ["HTTP_X_FORWARDED_FOR"] != "0.1.0.2") {
	logger ( LL_SYS, "I don't know who you are" );
	exit ();
}

function tick() {
	echo "DEFAULT Tick\n";

	// $str = ob_print_r ( $_SERVER );
	// if (@$_SERVER ["HTTP_X_FORWARDED_FOR"] != "0.1.0.2") {
	// logger ( LL_SYS, "I don't know who you are" );
	// } else {
	// $str = "I know who you are, and I like you" . $str;
	// }
	// $store = new BlockStore ();
	// $store->putContents ( timestampNow (), $str );
}

logger ( LL_DEBUG, "tick(): started" );
$call_delay = 10; // Every this many seconds
$last_tick = 59;

echo "************************************************************************************************************************************\n";

// Do the setup and clear up
echo "tick(): Performing Housekeeping\n";

$start = microtime ( true );
$end = timestamp2Time ( timestampFormat ( timestampNow (), "YmdHi" ) . sprintf ( "%02d", $last_tick ) );
echo "Building tick scheduler\n";
echo "    Start: " . timestampFormat ( time2Timestamp ( floor ( $start ) ), "Y-m-d H:i:s" ) . ", end: " . timestampFormat ( time2Timestamp ( floor ( $end ) ), "Y-m-d H:i:s" ) . ", step: " . $call_delay . "\n";

$secs = array ();
// echo "Starting point: " . floor ( $start ) . ", end point: " . $end . ", step: " . $call_delay . "\n";
for($s = floor ( $start ); $s <= $end; $s += $call_delay) {
	// echo " Adding: $s (" . timestampFormat ( time2Timestamp ( floor ( $s ) ), "H:i:s" ) . ")\n";
	echo "    Adding: " . timestampFormat ( time2Timestamp ( floor ( $s ) ), "H:i:s" ) . "\n";
	$secs [] = $s;
}

$tsnow = timestampNow ();

echo "\ntick(): Startup at " . timestampFormat ( $tsnow, "Y-m-d\TH:i:s T" ) . "\n";
foreach ( $secs as $k => $s ) {
	$now = microTime ( true );
	$tsnow = timestampNow ();
	if (ceil ( $secs [$k] - $now ) >= 0) {
		echo "--------------------------------------------------------------------------------------\n";
		echo "tick(): " . timestampFormat ( $tsnow, "H:i:s " ) . ": Start loop\n";
		tick ();
		echo "tick(): " . timestampFormat ( $tsnow, "H:i:s " ) . ": End loop\n";
		echo "--------------------------------------------------------------------------------------\n";

		$now = microTime ( true );
		$tsnow = time2Timestamp ( floor ( $now ) );

		echo "tick(): " . timestampFormat ( $tsnow, "H:i:s " ) . ": ";
		if (isset ( $secs [$k + 1] )) {
			$wake = $secs [$k + 1];
			$sleep = ($wake - $now);
			if ($sleep > 0) {
				echo "sleeping for " . sprintf ( "%0.2f", $sleep ) . " seconds\n";
				usleep ( $sleep * 1000000 );
			} else {
				echo "overran slot by " . sprintf ( "%0.2f", - $sleep ) . " seconds\n";
			}
		} else {
			echo "Job done\n";
		}
	} else {
		echo "tick(): skipping loop in the past\n";
	}
}

echo "************************************************************************************************************************************\n";

logger ( LL_DEBUG, "tick(): completed" );

InfoStore::getInstance ()->setInfo ( cronTickDebugInfoKey (), "Completed: " . timestampFormat ( timestampNow (), "Y/m/d H:i:s" ) );

?>
