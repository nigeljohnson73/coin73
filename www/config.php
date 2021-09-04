<?php
$app_name = "COIN73";
$api_host = "";

if($_SERVER["SERVER_NAME"] == "localhost") {
	$app_name .= " (Dev)";
	$api_host = "http://localhost:8081";
}
?>