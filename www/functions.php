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
	return $str;
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
		$matches = array ();
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
	$response->getBody ()->write ( json_encode ( $ret ) );
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

function graphData($package, $x = 260, $y = 80) {
	global $DEBUG;

	$x_min = $package->x_min;
	$x_max = $package->x_max;
	$x_major = isset ( $package->x_major ) ? ($package->x_major) : (0);
	$x_minor = isset ( $package->x_minor ) ? ($package->x_minor) : (0);
	$x_tgt = isset ( $package->x_tgt ) ? ($package->x_tgt) : (null);
	$x_swt = isset ( $package->x_swt ) ? ($package->x_swt) : (0);
	$y_min = $package->y_min;
	$y_max = $package->y_max;
	$y_major = isset ( $package->y_major ) ? ($package->y_major) : (0);
	$y_minor = isset ( $package->y_minor ) ? ($package->y_minor) : (0);
	$values = $package->values;

	$img_width = $x;
	$img_height = $y;
	$margins = 20;
	$graph_width = $img_width - $margins * 2;
	$graph_height = $img_height - $margins * 2;

	$img = imagecreatetruecolor ( $img_width, $img_height );
	imageantialias ( $img, true );
	// $font=imageLoadFont(dirname(__FILE__)."/../fonts/andalemo.ttf");
	$font = 4;

	$background_color = imagecolorallocate ( $img, 0x99, 0x99, 0x99 );
	$border_color = imagecolorallocate ( $img, 0x99, 0x99, 0x99 );
	$line_color = imagecolorallocate ( $img, 0x00, 0x99, 0x00 );
	$grid_major_color = imagecolorallocate ( $img, 0x77, 0x77, 0x77 );
	$grid_minor_color = imagecolorallocate ( $img, 0x90, 0x90, 0x90 );
	$sweet_color = imagecolorallocate ( $img, 0xaa, 0xaa, 0x99 );

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

				if (round ( $x1 ) >= 0) {
					imageLine ( $img, round ( $x1 ), round ( $y1 ), round ( $x2 ), round ( $y2 ), $grid_major_color );
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