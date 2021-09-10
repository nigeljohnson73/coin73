<?php
// Override this in config_override.php for a competely blank DataStore area of your own.
$localdev_namespace = "localdev";

// The project id is the google application project short code. Change this and the world is a 
// different place as this is where all the management and identity stuff happend operationally.
$project_id = "coin73";

// The CORS origin is used by the API so that other websites cannot host links to it.
// This value will by the server domain that the application will be served from.
// It is overwritten for localhost to support local testing.
$api_CORS_origin = "https://coin73.appspot.com";

// This is the public API server so that 'remote' calls in the application can be handled.
// Local API calls are managed through the local application, but remote ones are run through
// a separate service to allow for sideways scaling.
// It is overwritten for localhost to support local testing.
$api_host = $api_CORS_origin."/api/";

// This is the main host used in the application. It is used in emails and texts etc. It is
// Overwritten in we are on localhost dev server
$www_host = $api_CORS_origin."/";

// When re/validating user accounts a Multifactor authentication mechanism is used, a bit like the 
// Microsoft one. When you perfrom the action you are told a word, and in the authentication stage
// you are offred several options. This defines how many options.
$mfa_word_count = 5;

// This is storage for the RECAPTCHA keys that are set up to the live domain, as well as localhost.
$recaptcha_site_key = "";
$recaptcha_secret_key = "";

// This is the configuration for GMail SMTP. The username is generally your username and your
// password is an app password you need to set up. See the README.md file for more info.
$smtp_server = "smtp.gmail.com";
$smtp_port = "465";
$smtp_auth = true;
$smtp_security = "ssl"; // 'tls' | 'ssl'
$smtp_from_name = "Coin Admin";
$smtp_from_email = "";
$smtp_username = "";
$smtp_password = "";

?>