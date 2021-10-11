<?php
include_once(__DIR__ . "/../www/functions.php");
$miner_test = false;
$miner_submit = false;

$b = new StdClass();

// $b->coinbase_public_key = $coinbase_pubKey;
// $b->coinbase_private_key = $coinbase_privKey;
$b->recaptcha_site_key = $recaptcha_site_key;
$b->recaptcha_secret_key = $recaptcha_secret_key;
$b->smtp_name = str_replace($local_monika, "", $smtp_from_name);
$b->smtp_email = $smtp_from_email;
$b->smtp_password = $smtp_password;
$data = json_encode($b);
// file_put_contents ( "bundle.json", $data );

echo $data . "\n";
