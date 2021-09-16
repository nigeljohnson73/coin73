<?php
// This file contains configuration that is not part of the software repo but
// is part of the the production system.

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