<?php
$localdev_namespace = "localdev"; // Override this in config_override.php for a competely blank area of your own

$project_id = "coin73"; // Dont change this or authentication things will break
$api_CORS_origin = "https://coin73.appspot.com/"; // automatically overwritten if we are on localdev unless public with "*";
                                                  // $api_CORS_origin = "*"; // This makes the API interface public
$api_host = "https://coin73.appspot.com/api/"; // This is the public server - it's changed locally if localhost is detected

$recaptcha_site_key = "6LddrUgcAAAAAEqZZuTgIZpDUT4KcNbqknFsCLyP";
$recaptcha_secret_key = "6LddrUgcAAAAADqGmRqc85CRrJPXaPhUrfitAHnx";
?>