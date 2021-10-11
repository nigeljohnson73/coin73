<?php
ob_start();
$or = $response;
$response = null;
include_once(__DIR__ . "/submit_json.php");
$response = $or;
$json = ob_get_contents();
ob_end_clean();

$obj = json_decode($json);

// echo "--------------------\n";
// echo "JSON STRING: " . $json . "\n";
// echo "--------------------\n";
// echo "JSON OBJECT: " . ob_print_r ( $obj ) . "\n";
// echo "--------------------\n";

$resp = "N ";
if ($obj->success) {
	$resp = "Y ";
	// 	$resp .= $obj->data->job_id . " ";
	// 	$resp .= $obj->data->hash . " ";
	// 	$resp .= (($obj->data->difficulty < 10) ? ("0") : ("")) . $obj->data->difficulty . " ";
	// 	$resp .= (($obj->data->target_seconds < 10) ? ("0") : ("")) . $obj->data->target_seconds . " ";
} else {
	$resp .= $obj->reason;
}

$response->getBody()->write($resp);
