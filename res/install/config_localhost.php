<?php
// This file contains configuration that is only pertinent to the server
// that it's named after. It will be uploaded to the live server but will
// not be acted upon there. This file is also not part of the software
// repo, so it is safe to add whatever you like in here.
// Load order is as follows:
//
// * config.php
// * config_override.php
// * config.[[HOSTNAME]].php

// Locally debug a little more conversationally
$log_level = LL_INF;

// Have a google data storage area all to yourself (if using GAE)
// $localdev_namespace = "NigelsNamespace";

// Override this to update titles so you can tell which system you're on.
$local_monika = " (Dev)";

// If you are running php local servers, you'll want to run this
// $api_CORS_origin = "http://localhost:8080";
// $api_host = "http://localhost:8085/api/";
// $www_host = "http://localhost:8080/";

// Set the system key pair. Once you have a $localdev_namespace setup,
// run the key extractor: `__getOverlordKeys()` and put the values in here
// $coinbase_pubKey = "";
// $coinbase_privKey = "";

$use_gae = false;
