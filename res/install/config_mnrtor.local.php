<?php
include_once (__DIR__ . "/config_localhost.php");

// This file contains configuration that is only pertinent to the server
// that it's named after. It will be uploaded to the live server but will
// not be acted upon there. This file is also not part of the software
// repo, so it is safe to add whatever you like in here.
// Load order is as follows:
//
// * config.php
// * config_override.php
// * config.[[HOSTNAME]].php
$local_monika = " (MDev)";
?>