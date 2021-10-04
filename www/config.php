<?php
// Override this in config_[hostname].php for a competely blank DataStore area of your own.
// This is irellevant if you are using MySQL locally
$localdev_namespace = "localdev";

// The project id is the google application project short code. Change this and the world is a
// different place as this is where all the management and identity stuff happend operationally.
$project_id = "coin73";

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

// If you are being a douche, the API will block for this long. Be less of a douche
$miner_submit_punish_sec = 15;

// how much should every subsequent miner degrade (0-1 as a percent);
$miner_efficiency_degrade = 0.2;

// how many miners are allowed on a generic account
$miner_max_count = 5;

// the number of zeros at the beginning of the output from hash("sha1", $sig.$nonce) - defined by
// the lowest powered device taking between $miner_submit_target_sec and
// ($miner_submit_target_sec - 1) seconds to execute
$miner_difficulty = 3;

// Manage the limits for processing transactions into blocks
$transactions_per_block = 3000;
$transactions_per_page = 500;

// Used to validate passwrods for users of the site. This says at least 8 chars long, at least
// 1 upper case character, 1 lower case character, 1 digit, and one of !@#$%^&*
$valid_password_regex = "^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#\$%\^&\*])(?=.{8,})";

// How do we identify the coinbase in transactions for things like miner rewards etc.
$coinbase = "SupremeOverlord";

// The keypair just ensure faking cannot occur without access to the specific software config.
// These values should be setup in the system and stored here to reduce the load on the DataStore.
// Override them in the config_override.php
$coinbase_pubKey = "";
$coinbase_privKey = "";

// This is storage for the RECAPTCHA keys that are set up to the live domain, as well as localhost.
// Expected to be in config_override.php
$recaptcha_site_key = "";
$recaptcha_secret_key = "";

// This is the configuration for GMail SMTP. The username is generally your email address and your
// password is an app password you need to set up. See the README.md file for more info on that.
// Expected to be overridden in config_override.php
$smtp_server = "smtp.gmail.com";
$smtp_port = "465";
$smtp_auth = true;
$smtp_security = "ssl"; // 'tls' | 'ssl'
$smtp_from_name = "Coin Admin";
$smtp_from_email = "";
$smtp_username = "";
$smtp_password = "";

// Key fields for info - just to stop typos consuming hours of debugging
$info_key_circulation = "info_circulation";
$info_key_mined_shares = "info_mined_shares";
$info_key_last_block_hash = "info_last_block_hash";
$info_key_last_block_count = "info_block_count";

$info_key_cron_tick_debug = "cron_tick_debug";
$info_key_cron_minute_debug = "cron_minute_debug";
$info_key_cron_hour_debug = "cron_hour_debug";
$info_key_cron_day_debug = "cron_day_debug";

$switch_key_enabled = "ENABLED";
$switch_key_signup = "switch_signup";
$switch_key_login = "switch_login";
$switch_key_mining = "switch_mining";
$switch_key_block_creation = "switch_block_creation";
$switch_key_block_busy = "switch_block_busy";
$switch_key_transactions = "switch_transactions";
$switch_key_cron = "switch_cron";

// This is used in the message of miner rewards so that mined shares can be attributed
$miner_reward_tag = "-X- Miner Reward -X-";

// This defines the tag that will appear after the title and email name so you can tell which system
// you are working with. This should only be overwritten in the config_localhost.php file.
$local_monika = "";

// When running in non-localhost, you can force these to false so that you can debug javascript
$compress_js = true;

// Whether we are using Google App Engine and associated resources. If we do not, then use MySQL
// and the assocated file system in the DataStore and FileStore classes
$use_gae = false;
$db_server = "localhost";
$db_name = "cc";
$db_user = "cc_user";
$db_pass = "cc_passwd";

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
define ( "LL_EDBG", 5 );
define ( "LL_EDEBUG", 5 );
define ( "LL_XDBG", 6 );
define ( "LL_XDEBUG", 6 );

// Fundamentally disable logging in the system - can be overriden in config_[hostname].php;
$log_level = LL_SYS;
?>
