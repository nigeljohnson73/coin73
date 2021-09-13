<?php
include_once (__DIR__ . "/vendor/autoload.php");

// Poormans MFA like Microsoft
$mfa_words = array ();
$mfa_words [] = "Wedensday";
$mfa_words [] = "Helper";
$mfa_words [] = "Utility";
$mfa_words [] = "Layout";
$mfa_words [] = "Floating";
$mfa_words [] = "Efficient";
$mfa_words [] = "Miner";
$mfa_words [] = "Apple";
$mfa_words [] = "Verify";
$mfa_words [] = "Brand";
$mfa_words [] = "Power";
$mfa_words [] = "Private";
$mfa_words [] = "About";
$mfa_words [] = "Document";
$mfa_words [] = "Manual";
$mfa_words [] = "Server";
$mfa_words [] = "Home";
$mfa_words [] = "Arrow";
$mfa_words [] = "Keyboard";
$mfa_words [] = "Words";
$mfa_words [] = "Change";
$mfa_words [] = "Number";
$mfa_words [] = "Letter";
$mfa_words [] = "Reduce";
$mfa_words [] = "Website";
$mfa_words [] = "Printer";
$mfa_words [] = "Flask";
$mfa_words [] = "Reverse";
$mfa_words [] = "Change";
$mfa_words [] = "Value";
$mfa_words [] = "Slice";
$mfa_words [] = "Email";
$mfa_words [] = "Pencil";
$mfa_words [] = "Ruler";

function getProjectId() {
	global $project_id;
	return $project_id;
}

$data_namespace = "production";

// Overwritten if we are @locahost
function getDataNamespace() {
	global $data_namespace;
	return $data_namespace;
}

function getAppName() {
	global $config;
	return $config->name;
}

function getAppTitle() {
	global $config;
	return $config->title;
}

function getAppDate() {
	global $config;
	return $config->app_date;
}

function getApiDate() {
	global $config;
	return $config->api_date;
}

function getAppVersion() {
	global $config;
	return $config->major_version . "." . $config->minor_version . " (" . $config->status . ")";
}

function getApiHost() {
	global $api_host;
	return $api_host;
}

function getRecaptchaSiteKey() {
	global $recaptcha_site_key;
	return $recaptcha_site_key;
}

function getRecaptchaSecretKey() {
	global $recaptcha_secret_key;
	return $recaptcha_secret_key;
}

function actionGraceDays() {
	global $action_grace_days;
	return $action_grace_days;
}

function revalidationPeriodDays() {
	global $revalidation_period_days;
	return $revalidation_period_days;
}

function tokenTimeoutHours() {
	global $token_timeout_hours;
	return $token_timeout_hours;
}

function mfaWordCount() {
	global $mfa_word_count;
	return $mfa_word_count;
}

function validPasswordRegex() {
	global $valid_password_regex;
	return $valid_password_regex;
}

// Allow for capitalism tiers
function minerRewardTargetPerDay($wallet_id = null) {
	global $miner_reward_target_day;
	return $miner_reward_target_day;
}

// Allow for capitalism tiers
function minerSubmitTargetSeconds($wallet_id = null) {
	global $miner_submit_target_sec;
	return $miner_submit_target_sec;
}

// Allow for capitalism tiers
function minerSubmitMinSeconds($wallet_id = null) {
	return minerSubmitTargetSeconds ( $wallet_id ) / 2;
}

// Allow for capitalism tiers
function minerSubmitMaxSeconds($wallet_id = null) {
	return minerSubmitTargetSeconds ( $wallet_id ) * 2;
}

// Allow for capitalism tiers
function minerEfficiencyDegradation($wallet_id = null) {
	global $miner_efficiency_degrade;
	return $miner_efficiency_degrade;
}

// Allow for capitalism tiers
function minerMaxCount($wallet_id = null) {
	global $miner_max_count;
	return $miner_max_count;
}

function minerDifficulty() {
	global $miner_difficulty;
	return $miner_difficulty;
}

function ob_print_r($what) {
	ob_start ();
	print_r ( $what );
	$c = ob_get_contents ();
	ob_end_clean ();
	return $c;
}

// Transform data sent back in the app.js and app.css packing stuff or the wiki pages
function processSendableFile($str) {
	$str = str_replace ( "{{APP_NAME}}", getAppName (), $str );
	$str = str_replace ( "{{API_HOST}}", getApiHost (), $str );
	$str = str_replace ( "{{API_DATE}}", getApiDate (), $str );
	$str = str_replace ( "{{APP_DATE}}", getAppDate (), $str );
	$str = str_replace ( "{{APP_VERSION}}", getAppVersion (), $str );
	$str = str_replace ( "{{RECAPTCHA_SITE_KEY}}", getRecaptchaSiteKey (), $str );
	$str = str_replace ( "{{ACTION_GRACE_DAYS}}", actionGraceDays (), $str );
	$str = str_replace ( "{{TOKEN_TIMEOUT_HOURS}}", tokenTimeoutHours (), $str );
	$str = str_replace ( "{{REVALIDATION_PERIOD_DAYS}}", revalidationPeriodDays (), $str );
	$str = str_replace ( "{{MFA_WORD_COUNT}}", mfaWordCount (), $str );
	$str = str_replace ( "{{VALID_PASSWORD_REGEX}}", validPasswordRegex (), $str );
	$str = str_replace ( "{{MINER_REWARD_TARGET_DAY}}", minerRewardTargetPerDay (), $str );
	$str = str_replace ( "{{MINER_SUBMIT_TARGET_SEC}}", minerSubmitTargetSeconds (), $str );
	$str = str_replace ( "{{MINER_MAX_COUNT}}", minerMaxCount (), $str );
	$str = str_replace ( "{{MINER_DIFFICULTY}}", minerDifficulty (), $str );
	$str = str_replace ( "{{MINER_SUBMIT_TARGET_REWARD_PERCENT}}", rtrim ( rtrim ( number_format ( (submissionReward ( minerSubmitTargetSeconds () )) * 100, 1 ), "0" ), "." ) . "%", $str );
	$str = str_replace ( "{{MINER_DEGREDATION_PERCENT}}", rtrim ( rtrim ( number_format ( (minerEfficiencyDegradation ()) * 100, 1 ), "0" ), "." ) . "%", $str );
	$str = str_replace ( "{{MINER_PERCEIVED_MAX}}", rtrim ( rtrim ( number_Format ( totalMinerEfficiency (), 2 ), "0" ), "." ), $str );
	$str = str_replace ( "{{ACCOUNT_MINED_COINS_PER_DAY}}", rtrim ( rtrim ( number_Format ( totalMinedCoinsPerDay (), 2 ), "0" ), "." ), $str );

	return $str;
}

// The target reward calcuation
function submissionReward($s, $wallet_id = null) {
	$t = minerSubmitTargetSeconds ( $wallet_id );
	// Tweak by going here: http://localhost:8080/gfx/submission_time.png
	// $sc = 14; // t=15: 14, t=10: 8.5, t=5: 2; // Provides for some scaling of the S curve along the x-axis.
	// $xd = 2.22; // t=15: 2.22, t=10: 1.5, t=5: 1; // Provides an x-shift
	$sc = 1.140099116 * $t - 3.1;
	$xd = 0.1644 * $t - 0.1;
	
	// 15, 1.140099116
	// 2, 
	return 1 / (1 + exp ( - ($t - $sc) * ($s - ($t - $xd)) ));
}

// If you had $n miners, what is the effective actual mining amount
function degradedMinerEfficiency($n) {
	$delt = minerEfficiencyDegradation ();
	if ($n == 1) {
		return 1;
	}
	return degradedMinerEfficiency ( $n - 1 ) * (1 - $delt);
}

function totalMinerEfficiency() {
	$tot = 0;
	for($xx = 1; $xx <= minerMaxCount (); $xx ++) {
		$tot += degradedMinerEfficiency ( $xx );
	}
	return $tot;
}

function totalMinedCoinsPerDay() {
	$sub_reward = submissionReward ( minerSubmitTargetSeconds () );
	$tot_efficiency = totalMinerEfficiency ();
	$tot_coin = minerRewardTargetPerDay ();

	return $tot_coin * $sub_reward * $tot_efficiency;
}

function directoryListing($dirname, $extensoes = null) {
	if ($extensoes === null) {
		$extensoes = array (
				".*"
		);
	} else if (! is_array ( $extensoes )) {
		$extensoes = explode ( ",", $extensoes );
	}

	$files = array ();
	$dir = @ opendir ( $dirname );
	while ( $dir && false !== ($file = readdir ( $dir )) ) {
		// $matches = array ();
		if ($file != "." && $file != ".." && $file != ".svn") {
			for($i = 0; $i < count ( $extensoes ); $i ++) {
				if ($extensoes [$i] [0] == "*") {
					$extensoes [$i] = "." . $extensoes [$i];
				}
				if (preg_match ( "/" . $extensoes [$i] . "/i", $file )) {
					// if (ereg("\.+" . $extensoes[$i] . "$", $file)) {
					$files [] = $dirname . "/" . $file;
				}
			}
		}
	}

	@ closedir ( $dirname );
	sort ( $files );
	return $files;
}

function includeDirectory($d, $ext = "php") {
	$ret = array ();
	$files = directoryListing ( $d, $ext );
	foreach ( $files as $file ) {
		// echo "loading $file<br />";
		if (! preg_match ( '/index.php$/', $file )) {
			$ret [] = $file;
		}
	}
	return $ret;
}

function tfn($v, $quote = '') {
	if ($v === true)
		return "true";
	if ($v === false)
		return "false";
	if ($v === null)
		return "null";
	return $quote . $v . $quote;
}

function newestFile($p = ".") {
	$mtime = 0;
	$mfile = "";

	$files = directoryListing ( $p );
	foreach ( $files as $file ) {
		$fn = str_replace ( $p . "/", "", $file );
		$dot = strpos ( $fn, "." );
		if ($dot !== 0) {
			$mt = filemtime ( $file );
			if (is_dir ( $file )) {
				$n = newestFile ( $file );
				if ($n [0] > $mtime) {
					$mtime = $n [0];
					$mfile = $n [1];
				}
			} else if ($mt > $mtime) {
				$mtime = $mt;
				$mfile = $file;
			}
		}
	}

	return array (
			$mtime,
			$mfile,
			date ( "Y/m/d H:i:s", $mtime )
	);
}

function startJsonResponse() {
	ob_start ();
	return new StdClass ();
}

function endJsonResponse($response, $ret, $success = true, $message = "") {
	// for($i = 0; $i < 20; $i++){
	// echo GUIDv4(). "\n";
	// }
	// global $response;
	$c = ob_get_contents ();
	ob_end_clean ();

	$c = trim ( $c );
	if (strlen ( $c )) {
		$c = explode ( PHP_EOL, $c );
	}
	$ret->success = $success;
	$ret->status = $success ? "OK" : "FAIL";
	$ret->console = $c;
	$ret->message = $message;

	$resp = json_encode ( $ret );
	if ($response) {
		$response->getBody ()->write ( $resp );
	} else {
		echo $resp;
	}
}

function startPage() {
	ob_start ();
}

function endPage($compress = false, $strip_comments = true) {
	$odirty = ob_get_contents ();
	$dirty = $odirty;
	if ($strip_comments) {
		$dirty = preg_replace ( '/<!--(.|\s)*?-->/m', '', $dirty );
		$dirty = preg_replace ( '/^\w*[\r\n]+/m', '', $dirty );
	}
	ob_end_clean ();
	if ($compress) {
		libxml_use_internal_errors ( true );
		$x = new DOMDocument ();
		$x->loadHTML ( $dirty );
		$clean = $x->saveHTML ();
		if ($_SERVER ["SERVER_NAME"] == "localhost") {
			echo "<!-- RUNNING ON DEV HOST -->\n";
		}
		echo "<!-- COIN73 - (c) 2020 - " . date ( 'Y' ) . " Nigel Johnson, all rights reserved -->\n";
		echo "<!-- uncompressed: " . number_format ( strlen ( $odirty ), 0 ) . " bytes, compressed: " . number_format ( strlen ( $clean ), 0 ) . " bytes -->\n";
		// echo "<!-- \n";
		// print_r($_SERVER);
		// echo "-->\n";
		echo $clean;
	} else {
		echo $dirty;
	}
}

function msTime() {
	return microtime ( true );
	// $mt = explode(' ', microtime());
	// return ((int)$mt[1]) * 1000 + ((int)round($mt[0] * 1000));
}

function numDays($d) {
	return $d * 24 * 60 * 60;
}

function timestamp($day, $mon, $year, $hour = 0, $minute = 0, $second = 0) {
	$day = str_pad ( (( int ) $day) + 0, 2, "0", STR_PAD_LEFT );
	$mon = str_pad ( (( int ) $mon) + 0, 2, "0", STR_PAD_LEFT );
	$hour = str_pad ( (( int ) $hour) + 0, 2, "0", STR_PAD_LEFT );
	$minute = str_pad ( (( int ) $minute) + 0, 2, "0", STR_PAD_LEFT );
	$second = str_pad ( (( int ) $second) + 0, 2, "0", STR_PAD_LEFT );
	return $year . $mon . $day . $hour . $minute . $second;
}

function timestampNow() {
	global $date_overide;
	if (isset ( $date_overide )) {
		return $date_overide;
	}
	return adodb_date ( "YmdHis" );
}

function time2Timestamp($tm) {
	return adodb_date ( "YmdHis", $tm );
}

function timestamp2Time($ts) {
	// echo "timestamp2Time($ts): Got: '$ts'\n";
	$ts = str_replace ( " ", "", $ts );
	$ts = str_replace ( ":", "", $ts );
	$ts = str_replace ( "/", "", $ts );
	$ts = str_replace ( "-", "", $ts );
	$ts = str_replace ( ".", "", $ts );
	$ts = preg_replace ( "/[A-Z]*/", "", strtoupper ( $ts ) );
	$ts .= "000000"; // just in case I only suply a date

	// echo "timestamp2Time($ts): New ts: '$ts'\n";

	$year = substr ( $ts, 0, 4 );
	$month = substr ( $ts, 4, 2 );
	$day = substr ( $ts, 6, 2 );
	$hour = substr ( $ts, 8, 2 );
	$minute = substr ( $ts, 10, 2 );
	$second = substr ( $ts, 12, 2 );
	// echo "adodb_mktime($hour, $minute, $second, $month, $day, $year)\n";
	return adodb_mktime ( $hour, $minute, $second, $month, $day, $year );
}

function periodFormat($secs, $short = false) {
	$h = $secs / 3600;
	$hflag = ($short) ? ("h") : (" hour");
	$mflag = ($short) ? ("m") : (" min");
	// this takes a duration in seconds and outputs in hours and
	// minutes to the nearest minute - is use is for kind of "about" times.
	$estr = "";
	$hours = floor ( $h );
	if ($hours) {
		$hours = number_format ( $hours, 0 );
		$estr .= $hours . $hflag;
		if (! $short) {
			$pl = "s";
			if ($hours == 1) {
				$pl = "";
			}
			$estr .= $pl;
		}
	}

	$mins = $h - $hours;
	$mins *= 60;
	if ($mins) {
		$mins = ceil ( $mins );
		if (strlen ( $estr )) {
			$estr .= " ";
		}
		$estr .= $mins . $mflag;
		if (! $short) {
			$pl = "s";
			if ($mins == 1) {
				$pl = "";
			}
			$estr .= $pl;
		}
	}
	return $estr;
}

function durationFormat($secs, $use_nearest_sec = false) {
	if ($use_nearest_sec) {
		$secs = nearest ( $secs, 1 );
	}
	$sec_min = 60;
	$sec_hour = $sec_min * 60;
	$sec_day = $sec_hour * 24;

	$days = floor ( $secs / $sec_day );
	$secs -= $days * $sec_day;

	$hours = floor ( $secs / $sec_hour );
	$secs -= $hours * $sec_hour;

	$mins = floor ( $secs / $sec_min );
	$secs -= $mins * $sec_min;

	$ret = "";
	if ($days > 0) {
		$ret .= " " . $days . "d";
	}
	if ($hours > 0) {
		$ret .= " " . $hours . "h";
	}
	if ($mins > 0) {
		$ret .= " " . $mins . "m";
	}
	if ($use_nearest_sec) {
		$ret .= " " . $secs . "s";
	} else {
		$ret .= " " . number_format ( $secs, 3 ) . "s";
	}

	return trim ( $ret );
}

function durationStamp($secs, $use_us = false) {
	// echo "durationStamp(): started with $secs seconds\n";
	$sec_min = 60;
	$sec_hour = $sec_min * 60;
	$sec_day = $sec_hour * 24;

	$days = floor ( $secs / $sec_day );
	$secs -= $days * $sec_day;
	// echo "durationStamp(): days: $days\n";

	$hours = floor ( $secs / $sec_hour );
	$secs -= $hours * $sec_hour;
	// echo "durationStamp(): hours: $hours\n";

	$mins = floor ( $secs / $sec_min );
	$secs -= $mins * $sec_min;
	// echo "durationStamp(): mins: $mins\n";

	$ms = ($secs - (floor ( $secs ))) * 1000;
	$secs = floor ( $secs );

	$us = round ( ($ms - (floor ( $ms ))) * 1000 );
	$ms = floor ( $ms );

	// echo "durationStamp(): secs: $secs\n";
	// echo "durationStamp(): ms: $ms\n";
	// echo "durationStamp(): us: $us\n";

	$ret = "";
	if ($days > 0) {
		$ret .= (strlen ( $ret ) ? (" ") : ("")) . $days . "d";
	}
	if (strlen ( $ret ) || $hours > 0) {
		$ret .= (strlen ( $ret ) ? (" ") : ("")) . $hours . "h";
	}
	if (strlen ( $ret ) || $mins > 0) {
		$ret .= (strlen ( $ret ) ? (" ") : ("")) . $mins . "m";
	}
	if (strlen ( $ret ) || $secs > 0) {
		$ret .= (strlen ( $ret ) ? (" ") : ("")) . $secs . "s";
	}
	if (! $use_us || strlen ( $ret ) || $ms > 0) {
		$ret .= (strlen ( $ret ) ? (" ") : ("")) . $ms . "ms";
	}
	if ($use_us) {
		$ret .= (strlen ( $ret ) ? (" ") : ("")) . $us . "us";
	}

	return trim ( $ret );
}

function timestampFormat($ts, $format = null) {
	if ($format == null) {
		$format = "d/m/Y H:i:s";
	}
	$tm = timestamp2Time ( $ts );
	return adodb_date ( $format, $tm );
}

function timestampAdd($ts, $sec) {
	// default is add seconds
	$tm = timestamp2Time ( $ts );
	return time2Timestamp ( $tm + $sec );
}

function timestampAddDays($ts, $day) {
	return timestampAdd ( $ts, numDays ( $day ) );
}

function imageCenteredString(&$img, $font, $xMin, $xMax, $y, $str, $col) {
	$textWidth = imagefontwidth ( $font ) * strlen ( $str );
	$xLoc = ($xMax - $xMin - $textWidth) / 2 + $xMin + $font;
	imagestring ( $img, $font, $xLoc, $y, $str, $col );
}

function imageRightString(&$img, $font, $xMin, $xMax, $y, $str, $col) {
	$textWidth = imagefontwidth ( $font ) * strlen ( $str );
	$xLoc = ($xMax - $xMin - $textWidth) / 2 + $xMin + $font;
	imagestring ( $img, $font, $xLoc, $y, $str, $col );
}

function graphData($package, $x = 260, $y = 80) {
	global $DEBUG;

	$x_min = $package->x_min;
	$x_max = $package->x_max;
	$x_major = isset ( $package->x_major ) ? ($package->x_major) : (0);
	$x_minor = isset ( $package->x_minor ) ? ($package->x_minor) : (0);
	$x_major_scaler = isset ( $package->x_major_scaler ) ? ($package->x_major_scaler) : (null);
	$x_major_label = isset ( $package->x_major_label ) ? ($package->x_major_label) : ("");
	$x_tgt = isset ( $package->x_tgt ) ? ($package->x_tgt) : (null);
	$x_swt = isset ( $package->x_swt ) ? ($package->x_swt) : (0);
	$y_min = $package->y_min;
	$y_max = $package->y_max;
	$y_major = isset ( $package->y_major ) ? ($package->y_major) : (0);
	$y_minor = isset ( $package->y_minor ) ? ($package->y_minor) : (0);
	$y_major_scaler = isset ( $package->y_major_scaler ) ? ($package->y_major_scaler) : (null);
	$y_major_label = isset ( $package->y_major_label ) ? ($package->y_major_label) : ("");
	$values = $package->values;

	$background_color = (isset ( $package->background_color ) && is_array ( $package->background_color ) && (count ( $package->background_color ) == 3)) ? ($package->background_color) : ([ 
			0x99,
			0x99,
			0x99
	]);

	$border_color = (isset ( $package->border_color ) && is_array ( $package->border_color ) && (count ( $package->border_color ) == 3)) ? ($package->border_color) : ([ 
			0x99,
			0x99,
			0x99
	]);
	$line_color = (isset ( $package->line_color ) && is_array ( $package->line_color ) && (count ( $package->line_color ) == 3)) ? ($package->line_color) : ([ 
			0x00,
			0x99,
			0x00
	]);
	$grid_major_color = (isset ( $package->grid_major_color ) && is_array ( $package->grid_major_color ) && (count ( $package->grid_major_color ) == 3)) ? ($package->grid_major_color) : ([ 
			0x77,
			0x77,
			0x77
	]);
	$grid_minor_color = (isset ( $package->grid_minor_color ) && is_array ( $package->grid_minor_color ) && (count ( $package->grid_minor_color ) == 3)) ? ($package->grid_minor_color) : ([ 
			0x88,
			0x88,
			0x88
	]);
	$sweet_color = (isset ( $package->sweet_color ) && is_array ( $package->sweet_color ) && (count ( $package->sweet_color ) == 3)) ? ($package->sweet_color) : ([ 
			0xaa,
			0xaa,
			0x99
	]);
	$label_color = (isset ( $package->label_color ) && is_array ( $package->label_color ) && (count ( $package->label_color ) == 3)) ? ($package->label_color) : ([ 
			0x33,
			0x33,
			0x33
	]);

	$img_width = $x;
	$img_height = $y;
	$margins = 20;
	$graph_width = $img_width - $margins * 2;
	$graph_height = $img_height - $margins * 2;

	$img = imagecreatetruecolor ( $img_width, $img_height );
	imageantialias ( $img, true );
	// $font=imageLoadFont(dirname(__FILE__)."/../fonts/andalemo.ttf");
	$font = 4;

	$background_color = imagecolorallocate ( $img, $background_color [0], $background_color [1], $background_color [2] );
	$border_color = imagecolorallocate ( $img, $border_color [0], $border_color [1], $border_color [2] );
	$line_color = imagecolorallocate ( $img, $line_color [0], $line_color [1], $line_color [2] );
	$grid_major_color = imagecolorallocate ( $img, $grid_major_color [0], $grid_major_color [1], $grid_major_color [2] );
	$grid_minor_color = imagecolorallocate ( $img, $grid_minor_color [0], $grid_minor_color [1], $grid_minor_color [2] );
	$sweet_color = imagecolorallocate ( $img, $sweet_color [0], $sweet_color [1], $sweet_color [2] );
	$label_color = imagecolorallocate ( $img, $label_color [0], $label_color [1], $label_color [2] );

	imagefilledrectangle ( $img, 1, 1, $img_width - 2, $img_height - 2, $border_color );
	imagefilledrectangle ( $img, $margins, $margins, $img_width - 1 - $margins, $img_height - 1 - $margins, $background_color );

	if (count ( $values ) < 2 || abs ( max ( $values ) - min ( $values ) ) < 0.0001) {
		// if (1) {
		$txt = "Not enough data points";
		$xy = calcStringCenter ( $img, $txt, $font );
		imagestring ( $img, $font, $xy [0], $xy [1], $txt, $line_color );
	} else {
		if ($x_tgt !== null && $x_swt > 0) {
			// echo "xt: $x_tgt, xs: $x_swt<br />\n";
			$x1 = $margins + (((($x_tgt - $x_swt) - $x_min) / ($x_max - $x_min)) * $graph_width);
			$x2 = $margins + (((($x_tgt + $x_swt) - $x_min) / ($x_max - $x_min)) * $graph_width);

			$y1 = $margins;
			$y2 = $graph_height + $margins;

			$corners = array (
					$x1,
					$y1,
					$x2,
					$y1,
					$x2,
					$graph_height + $margins,
					$x1,
					$graph_height + $margins
			);

			imagefilledpolygon ( $img, $corners, 4, $sweet_color );
		}

		if ($x_minor > 0) {
			$pcnt = $x_minor / ($x_max - $x_min);
			for($i = 1; $i >= 0; $i -= $pcnt) {
				$x1 = $margins + ($i * $graph_width);
				$x2 = $x1;
				$y1 = $margins;
				$y2 = $graph_height + $margins;

				// if ($DEBUG)echo "gw: $graph_width, m: $margins, i: $i, x1: $x1<br />\n";
				if (round ( $x1 ) >= 0) {
					imageLine ( $img, round ( $x1 ), round ( $y1 ), round ( $x2 ), round ( $y2 ), $grid_minor_color );
				}
			}
		}

		if ($x_major > 0) {
			$pcnt = $x_major / ($x_max - $x_min);
			for($i = 1; $i >= 0; $i -= $pcnt) {
				$x1 = $margins + ($i * $graph_width);
				$x2 = $x1;
				$y1 = $margins;
				$y2 = $graph_height + $margins;
				$font = 2;

				if (round ( $x1 ) >= 0) {
					imageLine ( $img, round ( $x1 ), round ( $y1 ), round ( $x2 ), round ( $y2 ), $grid_major_color );
				}

				if ($x_major_scaler) {
					$label = (($i * (($x_max - $x_min))) + $x_min) . $x_major_label;

					imageCenteredString ( $img, $font, $x1, $x1, $y2 + 3, $label, $label_color );
				}
			}
		}

		// Draw 10% lines horizontally
		if ($y_minor) {
			$pcnt = $y_minor / $y_max;
			for($i = 0; $i <= 1; $i += $pcnt) {
				$y1 = $margins + ($i * $graph_height);
				$y2 = $y1;
				$x1 = $margins;
				$x2 = $graph_width + $margins;

				if (round ( $y1 ) >= 0) {
					imageLine ( $img, round ( $x1 ), round ( $y1 ), round ( $x2 ), round ( $y2 ), $grid_minor_color );
				}
			}
		}
		if ($y_major) {
			$pcnt = $y_major / $y_max;
			for($i = 0; $i <= 1; $i += $pcnt) {
				$y1 = $margins + ($i * $graph_height);
				$y2 = $y1;
				$x1 = $margins;
				$x2 = $graph_width + $margins;

				if (round ( $y1 ) >= 0) {
					imageLine ( $img, round ( $x1 ), round ( $y1 ), round ( $x2 ), round ( $y2 ), $grid_major_color );
				}

				if ($y_major_scaler) {
					$val_r = round ( ($y_major_scaler * ($y_max - ($i * (($y_max - $y_min))) + $y_min)), 2 );
					$val = number_format ( $val_r );
					$label = $val . $y_major_label;

					if ($i < (1 - $pcnt)) {
						imageRightString ( $img, $font, $x1, $x1, $y2 + 1, $label, $label_color );
					}
				}
			}
		}

		$npoints = count ( $values );

		// Make headroom so we are not dividing by zero below, and not having to check for it
		$max_value = $y_max; // max ( $values );
		$min_value = $y_min; // min ( $values );
		$min_delta = 0.001;
		if (abs ( $max_value - $min_value ) < $min_delta) {
			$min_value -= $min_delta / 2;
			$max_value += $min_delta / 2;
		}
		$ratio_x = ($graph_width) / ($npoints - 1);
		$ratio_y = ($graph_height) / ($max_value - $min_value);

		$vals = array_values ( $values );
		foreach ( $vals as $i => $value ) {
			if ($i > 0) {
				$x1 = $margins + ($i - 1) * $ratio_x;
				$x2 = $margins + ($i) * $ratio_x;
				$y1 = $margins + $graph_height - intval ( ($vals [$i - 1] - $min_value) * $ratio_y );
				$y2 = $margins + $graph_height - intval ( ($value - $min_value) * $ratio_y );

				imageLine ( $img, $x1, $y1, $x2, $y2, $line_color );
			}
		}
	}

	ob_start ();
	imagepng ( $img );
	$image_data = ob_get_contents ();
	ob_end_clean ();
	return $image_data;
}

function GUIDv4($trim = true) {
	// Windows
	if (function_exists ( 'com_create_guid' ) === true) {
		if ($trim === true)
			return trim ( com_create_guid (), '{}' );
		else
			return com_create_guid ();
	}

	// OSX/Linux
	if (function_exists ( 'openssl_random_pseudo_bytes' ) === true) {
		$data = openssl_random_pseudo_bytes ( 16 );
		$data [6] = chr ( ord ( $data [6] ) & 0x0f | 0x40 ); // set version to 0100
		$data [8] = chr ( ord ( $data [8] ) & 0x3f | 0x80 ); // set bits 6-7 to 10
		return vsprintf ( '%s%s-%s-%s-%s-%s%s%s', str_split ( bin2hex ( $data ), 4 ) );
	}

	// Fallback (PHP 4.2+)
	mt_srand ( ( double ) microtime () * 10000 );
	$charid = strtolower ( md5 ( uniqid ( rand (), true ) ) );
	$hyphen = chr ( 45 ); // "-"
	$lbrace = $trim ? "" : chr ( 123 ); // "{"
	$rbrace = $trim ? "" : chr ( 125 ); // "}"
	$guidv4 = $lbrace . substr ( $charid, 0, 8 ) . $hyphen . substr ( $charid, 8, 4 ) . $hyphen . substr ( $charid, 12, 4 ) . $hyphen . substr ( $charid, 16, 4 ) . $hyphen . substr ( $charid, 20, 12 ) . $rbrace;
	return $guidv4;
}

function httpErrorString($code) {
	$text = "Unknown error";
	if ($code !== NULL) {

		switch ($code) {
			case 100 :
				$text = 'Continue';
				break;
			case 101 :
				$text = 'Switching Protocols';
				break;
			case 200 :
				$text = 'OK';
				break;
			case 201 :
				$text = 'Created';
				break;
			case 202 :
				$text = 'Accepted';
				break;
			case 203 :
				$text = 'Non-Authoritative Information';
				break;
			case 204 :
				$text = 'No Content';
				break;
			case 205 :
				$text = 'Reset Content';
				break;
			case 206 :
				$text = 'Partial Content';
				break;
			case 300 :
				$text = 'Multiple Choices';
				break;
			case 301 :
				$text = 'Moved Permanently';
				break;
			case 302 :
				$text = 'Moved Temporarily';
				break;
			case 303 :
				$text = 'See Other';
				break;
			case 304 :
				$text = 'Not Modified';
				break;
			case 305 :
				$text = 'Use Proxy';
				break;
			case 400 :
				$text = 'Bad Request';
				break;
			case 401 :
				$text = 'Unauthorized';
				break;
			case 402 :
				$text = 'Payment Required';
				break;
			case 403 :
				$text = 'Forbidden';
				break;
			case 404 :
				$text = 'Not Found';
				break;
			case 405 :
				$text = 'Method Not Allowed';
				break;
			case 406 :
				$text = 'Not Acceptable';
				break;
			case 407 :
				$text = 'Proxy Authentication Required';
				break;
			case 408 :
				$text = 'Request Time-out';
				break;
			case 409 :
				$text = 'Conflict';
				break;
			case 410 :
				$text = 'Gone';
				break;
			case 411 :
				$text = 'Length Required';
				break;
			case 412 :
				$text = 'Precondition Failed';
				break;
			case 413 :
				$text = 'Request Entity Too Large';
				break;
			case 414 :
				$text = 'Request-URI Too Large';
				break;
			case 415 :
				$text = 'Unsupported Media Type';
				break;
			case 500 :
				$text = 'Internal Server Error';
				break;
			case 501 :
				$text = 'Not Implemented';
				break;
			case 502 :
				$text = 'Bad Gateway';
				break;
			case 503 :
				$text = 'Service Unavailable';
				break;
			case 504 :
				$text = 'Gateway Time-out';
				break;
			case 505 :
				$text = 'HTTP Version not supported';
				break;
		}
	}
	return $text;
}

// Calls a $url and returns a wrapped object. Pass in $post arguments as key/value array pairs
function jsonApi($url, $post = null, $timeout = null) {
	if ($timeout == null) {
		$timeout = 15;
	}
	$ch = curl_init ( $url );
	curl_setopt ( $ch, CURLOPT_CONNECTTIMEOUT, 0 ); // connecting timout is forever... unitl the curl excution timeout
	curl_setopt ( $ch, CURLOPT_TIMEOUT, $timeout ); // curl execution timeout in seconds
	curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );

	if ($post !== false) {
		if (! is_array ( $post )) {
			if (is_object ( $post )) {
				$post = ( array ) $post;
			} else {
				$post = array ();
			}
		}
		curl_setopt ( $ch, CURLOPT_POSTFIELDS, $post );
		logger ( LL_DBG, "jsonApi(): sending as POST request" );
		logger ( LL_DBG, ob_print_r ( $post ) );
	} else {
		logger ( LL_DBG, "jsonApi(): sending as GET request" );
	}

	$ret = new StdClass ();

	// execute!
	$pt = new ProcessTimer ();
	$data = curl_exec ( $ch );
	$api_t = $pt->duration ();
	$ret->duration = $api_t;
	logger ( LL_INF, "jsonApi(): call duration: " . durationStamp ( $api_t ) );

	$response = curl_getinfo ( $ch, CURLINFO_HTTP_CODE );
	$ll = LL_DBG;
	if ($response < 200 || $response > 299) {
		$ll = LL_WRN;
	}
	logger ( $ll, "jsonApi(): response (" . $response . ") - " . httpErrorString ( $response ) );
	$ret->response = $response;

	$ret->errno = false;
	$ret->error = "";
	// Check for errors and display the error message
	if ($errno = curl_errno ( $ch )) {
		$error_message = curl_strerror ( $errno );
		logger ( LL_ERR, "jsonApi(): error (" . $errno . "): " . $error_message );
		$ret->errno = $errno;
		$ret->error = $error_message;
	}
	$ret->data = json_decode ( $data );

	// echo "****************************************\n";
	// echo "GOT DATA:\n";
	// echo $data . "\n";
	// echo "****************************************\n";
	// echo ob_print_r($ret->data);
	// echo "****************************************\n";

	// close the connection, release resources used
	curl_close ( $ch );

	return $ret;
}

$inc = array ();
$inc [] = dirname ( __FILE__ ) . "/config.php";
$inc [] = dirname ( __FILE__ ) . "/config_override.php";
$inc = array_merge ( $inc, includeDirectory ( __DIR__ . "/_include" ) );
foreach ( $inc as $file ) {
	if (file_exists ( $file ) && ! is_dir ( $file )) {
		// echo "loading $file\n";
		include_once ($file);
	}
}

if (@$_SERVER ["SERVER_NAME"] == "localhost") {
	global $config;
	global $localdev_namespace;
	global $api_CORS_origin;
	global $api_host;
	global $www_host;
	global $data_namespace;

	// If we're on localdev/ update the config before we load it
	$config = json_decode ( file_get_contents ( __DIR__ . "/../version.json" ) );
	$config->app_date = newestFile ( __DIR__ . "/../www" ) [2];
	$config->api_date = newestFile ( __DIR__ . "/../api" ) [2];
	file_put_contents ( __DIR__ . "/config.json", json_encode ( $config ) );

	$config->title .= " (Dev)";
	$data_namespace = $localdev_namespace;
	$api_host = "http://localhost:8085/api/";
	$www_host = "http://localhost:8080/";
	if ($api_CORS_origin != "*") {
		$api_CORS_origin = "http://localhost:8080";
	}
} else {
	$config = json_decode ( file_get_contents ( __DIR__ . "/config.json" ) );
}

?>