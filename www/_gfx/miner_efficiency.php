<?php
include_once(dirname(__FILE__) . "/../functions.php");

$p = new StdClass();
$p->x_min = 1;
$p->x_max = minerMaxCount();
$p->x_major = 1; // Every 1 miner
$p->y_min = 0;
$p->y_max = minerMaxCount();
$p->y_major = 1; // Every 1 miner
$p->y_minor = 1 / 2; // Every 1 miner

$p->y_major_scaler = 1;
$p->y_major_label = "";
$p->x_major_scaler = 1;
$p->x_major_label = "";

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

$values = array();
for ($xx = 1; $xx <= $p->x_max; $xx++) {
	$yy = degradedMinerEfficiency($xx);
	$values[$xx] = $yy;
}

$p->values = array();
$tot = 0;
for ($xx = 1; $xx <= $p->x_max; $xx++) {
	$tot += $values[$xx];
	$p->values[$xx] = $tot;
}
$p->y_max = ceil($tot);

if (1) {
	header("Content-type:image/png");
}
echo graphData($p, 640, 340);
