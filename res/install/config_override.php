<?php
// This file contains configuration that is not part of the software repo but
// is part of the the production system.

// The project id is the google application project short code. Change this and the world is a
// different place. If you are doing your own dev in your own tennant, then you will need to change it.
// $project_id = "coin73";
// $api_CORS_origin = "https://".$project_id.".appspot.com";
// $api_host = $api_CORS_origin . "/api/";
// $www_host = $api_CORS_origin . "/";

// Set the production system key pair - override for localhost
$coinbase_pubKey = "";
$coinbase_privKey = "";

// Set the google RECAPTCHA keys
$recaptcha_site_key = "";
$recaptcha_secret_key = "";

// Set the system email details
// $smtp_from_name = "Coin Admin";
$smtp_from_email = "";
$smtp_username = $smtp_from_email;
$smtp_password = "";
?>