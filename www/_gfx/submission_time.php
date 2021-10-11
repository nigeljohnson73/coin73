<?php
include_once(dirname(__FILE__) . "/../functions.php");

$p = new StdClass();
$p->x_min = minerSubmitMinSeconds();
$p->x_max = minerSubmitMaxSeconds();
$p->x_major = 1; // Every 1 second
$p->x_minor = 1 / 2; // Every 1/2 second
$p->x_tgt = minerSubmitTargetSeconds(); // Target value
$p->x_swt = 0.25; // Sweet spot delta either side of the target
$p->y_min = 0;
$p->y_max = 1;
$p->y_major = 0.1;

$p->y_major_scaler = 100;
$p->y_major_label = "%";
$p->x_major_scaler = 1;
$p->x_major_label = "s";

$p->background_color = [
	0xcc,
	0xcc,
	0xcc
];
$p->border_color = [
	0xcc,
	0xcc,
	0xcc
];
$p->line_color = [
	0x00,
	0x99,
	0x00
];
$p->grid_major_color = [
	0xaa,
	0xaa,
	0xaa
];
$p->grid_minor_color = [
	0xdd,
	0xdd,
	0xdd
];
$p->sweet_color = [
	0xdd,
	0xdd,
	0x99
];
$p->label_color = [
	0x33,
	0x33,
	0x33
];

// Every 10%
$x_stp = 0.1;
$p->values = array();
for ($xx = $p->x_min; $xx < $p->x_max; $xx = $xx + $x_stp) {
	$yy = submissionReward($xx);
	$p->values["" . $xx] = $yy;
}

if (1) {
	header("Content-type:image/png");
}
echo graphData($p, 640, 340);
