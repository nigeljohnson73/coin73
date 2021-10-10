<?php
// https://minertor.appspot.com/benchmark?d=4&n=1000&LOG
include_once (__DIR__ . "/functions.php");

echo "<pre>\n";

if (isset ( $_GET ["LOG"] ) || is_cli ()) {
	global $logger;
	$logger->setLevel ( LL_DBG );
	logger ( LL_SYS, "Enabled logging" );
}
if (isset ( $_GET )) {
	logger ( LL_SYS, "Parameters: " . ob_print_r ( $_GET ) );
}

$difficulty = isset ( $_GET ["d"] ) ? $_GET ["d"] : 4;
$max_count = isset ( $_GET ["n"] ) ? max ( 10, min ( $_GET ["n"], 1000 ) ) : 1000;

$times = array ();
// $nano_times = array ();
$hashrates = array ();
$tstarted = microtime ( true );

// $difficulty = $d;
$times [$difficulty] = array ();
$iterations = 0;
$i = 0;
for(; $i < ($max_count - 1) && (microtime ( true ) - $tstarted) <= 20; $i ++) {
	$iterations = $iterations + 1;
	$hash = GUIDv4 () . rand ();
	$begins = str_pad ( "", $difficulty, "0" );
	$started = microtime ( true );

	for($n = 0, $done = false; ! $done; $n ++) {
		// while ( $nonce < 0 ) {
		// Calculate the signature hash
		$signed = hash ( "sha1", $hash . $n );

		// Check if the signature starts with the expected number of zeros
		if (strpos ( $signed, $begins ) === 0) { // If it has, we found one
			$duration = microtime ( true ) - $started;
			if ($duration < 0.001) {
				$duration = 0.001;
			}
			$times [$difficulty] [] = $duration;
			$hashrate = ($n + 1) / $duration;
			$hashrates [$difficulty] [] = $hashrate;

			$lstr = "";
			$lstr .= "Dif: $difficulty";
			$lstr .= ", Loop: ";
			$lstr .= str_pad ( ($i + 1), 3, " ", STR_PAD_LEFT );

			$lstr .= ", Hashrate: ";
			$lstr .= str_pad ( number_format ( $hashrate, 2 ), 13, " ", STR_PAD_LEFT );
			$lstr .= " h/sec";

			$lstr .= ", Duration: ";
			$lstr .= str_pad ( number_format ( $duration, 5 ), 8, " ", STR_PAD_LEFT );
			$lstr .= " seconds ";
			$lstr .= ", " . $signed;
			$lstr .= " - " . $n;

			logger ( LL_INF, $lstr );
			$done = true;
		}
	}
}

$xt = $times [$difficulty];
$hr = $hashrates [$difficulty];
$avghr = array_sum ( $hr ) / count ( $hr );
$avgxt = array_sum ( $xt ) / count ( $xt );
$maxhr = max ( $hr );
$minhr = min ( $hr );
// echo "min: $minhr\n";
// echo "max: $maxhr\n";

$spread = array ();
for($i = 0; $i < 10; $i ++) {
	$spread [$i] = 0;
}
foreach ( $hashrates [$difficulty] as $hr ) {
	// echo "hr: $hr\n";
	$b = $hr - $minhr;
	// echo " b: $b\n";
	$p = 100 * ($b / ($maxhr - $minhr));
	// echo " p: $p\n";
	$c = floor ( $p / 10 );
	// echo " c: $c\n";
	$spread [min ( $c, 9 )] += 1;
}
// ksort ( $spread );
// print_r ( $spread );

echo "\n";
logger ( LL_SYS, "Execution time: " . durationFormat ( (microtime ( true ) - $tstarted) ) );
logger ( LL_SYS, "Difficulty    : " . $difficulty );
logger ( LL_SYS, "Iterations    : " . number_format ( $iterations, 0 ) );
logger ( LL_SYS, "Solve time    : " . number_format ( $avgxt, 5 ) . "s" );
logger ( LL_SYS, "Min Hashrate  : " . number_format ( $minhr, 2 ) . " h/s" );
logger ( LL_SYS, "Avg Hashrate  : " . number_format ( $avghr, 2 ) . " h/s" );
logger ( LL_SYS, "Max Hashrate  : " . number_format ( $maxhr, 2 ) . " h/s" );
echo "\n";
logger ( LL_SYS, "Hashrate distribution " );
foreach ( $spread as $k => $v ) {
	$p = ($v / $iterations) * 100;
	// echo " c: $k, ";
	// echo " r: $v/$iterations, ";
	// echo " p: $p\n";
	$str = "   ";
	$str .= ($k > 0) ? (str_pad ( $k * 10, 2, " ", STR_PAD_LEFT ) . "%") : ("   ");
	$str .= " -> ";
	$str .= ($k < 9) ? (str_pad ( (($k + 1) * 10), 2, " ", STR_PAD_LEFT ) . "%") : ("   ");
	$str .= " : ";
	$str .= str_pad ( number_format ( $p, 2 ), 5, " ", STR_PAD_LEFT ) . "% :";
	$str .= str_pad ( "", round ( $p ), "*", STR_PAD_LEFT );
	logger ( LL_SYS, $str );
}
// echo "\n";
echo "</pre>";
?>