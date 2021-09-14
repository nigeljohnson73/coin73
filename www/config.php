<?php
// Override this in config_[hostname].php for a competely blank DataStore area of your own.
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
$api_host = $api_CORS_origin . "/api/";

// This is the main host used in the application. It is used in emails and texts etc. It is
// Overwritten in we are on localhost dev server
$www_host = $api_CORS_origin . "/";

// When re/validating user accounts a Multifactor authentication mechanism is used, a bit like the
// Microsoft one. When you perfrom the action you are told a word, and in the authentication stage
// you are offred several options. This defines how many options.
$mfa_word_count = 5;

// If an API fails, then we should delay a bit so it cannot be flooded. This setting defines the
// waiting period in seconds
$api_failure_delay = 3;

// Thisis how long you have to not be pestered to revalidate all the time.
$revalidation_period_days = 90;

// When the system alerts you that you need to perform an action, you will need to do it in this
// time before acction is taken against you.
$action_grace_days = 7;

// Any token created by the system will valid for this long.
$token_timeout_hours = 24;

// How many coins should be mined by a perfectly tailored single miner
$miner_reward_target_day = 5;

// The target submission time for each job
$miner_submit_target_sec = 15;

// how much should every subsequet miner degrade (0-1 as a percent);
$miner_efficiency_degrade = 0.2;

// how many miners are allowed on a generic account
$miner_max_count = 5;

// the number of zeros at the beginning of the output from hash("sha1", $sig.$nonce) - defined by
// the lowest powered device taking between $miner_submit_target_sec and 
// ($miner_submit_target_sec - 1) seconds to execute
$miner_difficulty = 2;

// Used to validate passwrods for users of the site. This says at least 8 chars long, at least
// 1 upper case character, 1 lower case character, 1 digit, and one of !@#$%^&*
$valid_password_regex = "^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#\$%\^&\*])(?=.{8,})";

// This is storage for the RECAPTCHA keys that are set up to the live domain, as well as localhost.
// Expected to be in config_override.php
$recaptcha_site_key = "";
$recaptcha_secret_key = "";

// This is the configuration for GMail SMTP. The username is generally your username and your
// password is an app password you need to set up. See the README.md file for more info on that.
// Expected to be in config_override.php
$smtp_server = "smtp.gmail.com";
$smtp_port = "465";
$smtp_auth = true;
$smtp_security = "ssl"; // 'tls' | 'ssl'
$smtp_from_name = "Coin Admin";
$smtp_from_email = "";
$smtp_username = "";
$smtp_password = "";

// Configure the logging variables
define ( "LL_NONE", 0 );
define ( "LL_SYSTEM", 0 );
define ( "LL_SYS", 0 );
define ( "LL_ERROR", 1 );
define ( "LL_ERR", 1 );
define ( "LL_WRN", 2 );
define ( "LL_WARN", 2 );
define ( "LL_WARNING", 2 );
define ( "LL_INF", 3 );
define ( "LL_INFO", 3 );
define ( "LL_DBG", 4 );
define ( "LL_DEBUG", 4 );
define ( "LL_EDEBUG", 5 );
define ( "LL_XDEBUG", 6 );

// Fundamentally disable logging in the system - can be overriden in config_[hostname].php;
$log_level = LL_SYS;

?>