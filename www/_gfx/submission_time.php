<?php
include_once (dirname ( __FILE__ ) . "/../functions.php");

// $DEBUG = false;

// $what = 0;
// if ($what == 0) {
	$x_tgt = 5;
	$x_stp = 0.1;

	$p = new StdClass ();
	$p->x_min = $x_tgt / 2;
	$p->x_max = 2 * $x_tgt;
	$p->x_tgt = $x_tgt;
	$p->x_major = 1; // Every 1 second
	$p->x_minor = 1 / 2; // Every 1/2 second
	$p->x_swt = 0.25;
	$p->y_min = 0;
	$p->y_max = 1;
	$p->y_major = 0.1;

	// Every 10%
	function sigmoid($s, $t) {
		return 1 / (1 + exp ( - ($t - 2) * ($s - ($t - 1)) ));
	}

	$p->values = array ();
	for($xx = $p->x_min; $xx < $p->x_max; $xx = $xx + $x_stp) {
		$yy = sigmoid ( $xx, $x_tgt );
		$p->values ["" . $xx] = $yy;
	}
// } else if ($what == 1) {
// 	$p = new StdClass ();
// 	$p->x_min = 1;
// 	$p->x_max = 10;
// 	$p->x_major = 1; // Every 1 miner
// 	$p->y_min = 0;
// 	$p->y_max = 1;
// 	$p->y_major = 0.1; // Every 10%
// 	$p->y_minor = 0.1 / 2; // Every 10%

// 	$delt = 0.2;

// 	function degrade($n, $delt) {
// 		$r = 1;
// 		if ($n == 1)
// 			return 1;
// 		return degrade ( $n - 1, $delt ) * (1 - $delt);
// 	}

// 	$p->values = array ();
// 	// $p->values[0]=1;
// 	for($xx = $p->x_min; $xx <= $p->x_max; $xx ++) {
// 		$yy = degrade ( $xx, $delt );
// 		if ($DEBUG)
// 			echo "x: $xx, y: $yy<br />\n";
// 		$p->values ["" . $xx] = $yy;
// 	}
// } else if ($what == 2) {
// 	$p = new StdClass ();
// 	$p->x_min = 1;
// 	$p->x_max = 10;
// 	$p->x_major = 1; // Every 1 miner
// 	$p->y_min = 0;
// 	$p->y_max = 10;
// 	$p->y_major = 1; // Every 1 miner
// 	$p->y_minor = 1 / 2; // Every 1 miner

// 	$delt = 0.2;

// 	// 10% degredation per next miner
// 	function degrade($n, $delt) {
// 		$r = 1;
// 		if ($n == 1)
// 			return 1;
// 		return degrade ( $n - 1, $delt ) * (1 - $delt);
// 	}

// 	$values = array ();
// 	// $p->values[0]=1;
// 	for($xx = 1; $xx <= $p->x_max; $xx ++) {
// 		$yy = degrade ( $xx, $delt );
// 		if ($DEBUG)
// 			echo "x: $xx, y: $yy<br />\n";
// 		$values [$xx] = $yy;
// 	}

// 	$p->values = array ();
// 	$tot = 0;
// 	for($xx = 1; $xx <= $p->x_max; $xx ++) {
// 		$tot += $values [$xx];
// 		$p->values [$xx] = $tot;
// 	}
// 	$p->y_max = ceil ( $tot );
// }

if (1)
	header ( "Content-type:image/png" );
echo graphData ( $p, 1040, 540 );

?>