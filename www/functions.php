<?php

function getProjectId() {
	global $project_id;
	return $project_id;
}

$data_namespace = "";

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
function ob_print_r($what) {
	ob_start ();
	print_r ( $what );
	$c = ob_get_contents ();
	ob_end_clean ();
	return $c;
}

// Transform data sent back in the app.js or app.css packing stuff
function processSendableFile($str) {
	$str = str_replace ( "{{API_HOST}}", getApiHost (), $str );
	$str = str_replace ( "{{API_DATE}}", getApiDate (), $str );
	$str = str_replace ( "{{APP_DATE}}", getAppDate (), $str );
	$str = str_replace ( "{{APP_VERSION}}", getAppVersion (), $str );
	$str = str_replace ( "{{RECAPTCHA_SITE_KEY}}", getRecaptchaSiteKey (), $str );
	// $str = str_replace ( "{{API_VERSION}}", getApiVersion (), $str );
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
	// global $response;
	$c = ob_get_contents ();
	ob_end_clean ();

	$ret->success = $success;
	$ret->status = $success ? "OK" : "FAIL";
	$ret->console = explode ( "\n", $c );
	$ret->message = $message;
	$response->getBody ()->write ( json_encode ( $ret ) );
}

function startPage() {
	ob_start ();
}

function endPage($compress = false, $strip_comments = true) {
	echo "<pre>\n";
	try {

		/*
		 * GDS\Schema::addString
		 * GDS\Schema::addInteger
		 * GDS\Schema::addDatetime
		 * GDS\Schema::addFloat
		 * GDS\Schema::addBoolean
		 * GDS\Schema::addStringList
		 *
		 * GDS\Schema::addGeopoint
		 * $obj_person->location = new GDS\Property\Geopoint(53.4723272, -2.2936314);
		 * echo $obj_person->location->getLatitude();
		 * echo $obj_person->location->getLongitude();
		 *
		 * Also some DateTime:
		 * 'published' => new DateTime('-5 years')
		 *
		 * $obj_book_store->fetchOne("SELECT * FROM Book WHERE isbn = @isbnNumber", [
		 * 'isbnNumber' => '1853260304'
		 * ]);
		 *
		 * $obj_book_store->fetchOne("SELECT * FROM Task WHERE date_date < @now", [
		 * 'now' => new DateTime()
		 * ]);
		 */

		// The Store accepts a Schema object or Kind name as its first parameter

		// Create a simple Entity object
		// $bookstore = new BookStore ();
		// $book = new GDS\Entity ();
		// $book->title = 'Romeo and Juliet';
		// $book->author = 'William Shakespeare';
		// $book->isbn = '1840224339';

		// $ret = $bookstore->insert ( $book );
		// Insert into the Datastore
		// $obj_book_store->upsert ( $obj_book );

		// $arr_books = $obj_book_store->fetchAll ();

		// $obj_schema = (new GDS\Schema('Book'))
		// ->addString('title')
		// ->addString('author')
		// ->addString('isbn');

		// // The Store accepts a Schema object or Kind name as its first parameter
		// $obj_book_store = new GDS\Store($obj_schema);

		// print_r ( $arr_books );
	} catch ( Exception $e ) {
		print_r ( $e );
	}
	echo "</pre>\n";

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

if ($_SERVER ["SERVER_NAME"] == "localhost") {
	global $config;
	global $localdev_namespace;
	global $api_CORS_origin;
	global $api_host;
	global $data_namespace;

	// If we're on localdev/ update the config before we load it
	$config = json_decode ( file_get_contents ( __DIR__ . "/../version.json" ) );
	$config->app_date = newestFile ( __DIR__ . "/../www" ) [2];
	$config->api_date = newestFile ( __DIR__ . "/../api" ) [2];
	file_put_contents ( __DIR__ . "/config.json", json_encode ( $config ) );

	$config->title .= " (Dev)";
	$data_namespace = $localdev_namespace;
	$api_host = "http://localhost:8085/api/";
	if ($api_CORS_origin != "*") {
		$api_CORS_origin = "http://localhost:8080";
	}
} else {
	$config = json_decode ( file_get_contents ( __DIR__ . "/config.json" ) );
}

?>