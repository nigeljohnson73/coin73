<?php
// ini_set ( 'memory_limit', '64M' );
// ini_set ( 'post_max_size', '32M' );
// ini_set ( 'upload_max_filesize', '32M' );
// error_reporting ( E_ALL );
// ini_set ( 'display_errors', 'on' );

// if ($_SERVER["SERVER_NAME"] == "localhost") {
// 	$ga_urchin_id = "UA-20772843-10";
// 	$app_title = "T5A (Dev)";
// } else {
// 	$ga_urchin_id = "UA-20772843-11";
// 	$app_title = "T5A";
// }

// include_once ('google/appengine/api/mail/Message.php');
// include_once ('google/appengine/api/cloud_storage/CloudStorageTools.php');
// use google\appengine\api\cloud_storage\CloudStorageTools;
// use google\appengine\api\mail\Message;
// use \Michelf\MarkdownExtra;

// function sendEmail($mail_options, $secondary_sender_email = null) {
// 	// format of mail options here:
// 	// https://googleappengine.googlecode.com/svn-history/r423/trunk/python/php/sdk/google/appengine/api/mail/BaseMessage.php
// 	// $attach = null;
// 	// if (isset ( $mail_options["attachArray"] )) {
// 	// $attach = $mail_options["attachArray"];
// 	// unset ( $mail_options["attachArray"] );
// 	// }

// 	// dbLogger ( "email", "called sendEmail()");
// 	try {
// 		$message = new Message ( $mail_options );
// 		// if ($attach) {
// 		// $message->addAttachmentArray ( $attach );
// 		// }
// 		// dbLogger ( "email", "Attepting to send as supplied");
// 		$message->send ();
// 		// dbLogger ( "email", "Message sent without throwing an exception");
// 	} catch ( Exception $e ) {
// 		// dbLogger ( "email", "Caught email send exception:");
// 		// dbLogger ( "email", $e->getMessage ());
// 		// dbLogger ( "email", "Trying send with short sender details");
// 		// known bug in GAE with sender names
// 		try {
// 			$mail_options["sender"] = $secondary_sender_email;
// 			$message = new Message ( $mail_options );
// 			// if ($attach) {
// 			// $message->addAttachmentArray ( $attach );
// 			// }
// 			$message->send ();
// 			// dbLogger ( "email", "Message sent without throwing an exception");
// 		} catch ( InvalidArgumentException $e ) {
// 			// dbLogger ( "email", "Caught email send exception again:");
// 			// dbLogger ( "email", $e->getMessage ());
// 			// unset ( $mail_options["textBody"] );
// 			// unset ( $mail_options["htmlBody"] );
// 			// print_r ( $mail_options );
// 			return $e;
// 		}
// 	}

// 	unset ( $mail_options["textBody"] );
// 	unset ( $mail_options["htmlBody"] );
// 	unset ( $mail_options["attachments"] );
// 	return true;
// }

function markdown($txt) {
	return MarkdownExtra::defaultTransform ( $txt );
}

// function getAppId() {
// 	$app_id = "";
// 	$c = file_get_contents ( dirname ( __FILE__ ) . "/app.yaml" );
// 	// echo "got ".strlen($c)." bytes from app.yaml\n";
// 	if (preg_match ( '/application: ([\w-]+)/', $c, $matches ) !== false) {
// 		// print_r($matches);
// 		if (count ( $matches )) {
// 			$app_id = trim ( $matches[1] );
// 		}
// 	}
// 	return $app_id;
// }

// function getCacheId() {
// 	$cache_id = "";
// 	$c = file_get_contents ( dirname ( __FILE__ ) . "/service-worker.js" );
// 	// echo "got ".strlen($c)." bytes from service-worker.js\n";
// 	// echo substr($c, 100, 50)."\n";
// 	if (preg_match ( "/CACHE_NAME = '(.*)'/", $c, $matches ) !== false) {
// 		// print_r($matches);
// 		if (count ( $matches )) {
// 			$cache_id = trim ( $matches[1] );
// 		}
// 	}
// 	return $cache_id;
// }

// function getAppEmailAddress($pick = null) {
// 	if ($pick == null) {
// 		$pick = "website";
// 	}
// 	return $pick . "@" . getAppId () . ".appspotmail.com";
// }

// function getAppVersion() {
// 	$app_version = "";
// 	$c = file_get_contents ( dirname ( __FILE__ ) . "/app.yaml" );
// 	if (preg_match ( '/version: ([\w-]+)/', $c, $matches ) !== false) {
// 		if (count ( $matches )) {
// 			$app_version = trim ( $matches[1] );
// 		}
// 	}
// 	return $app_version;
// }

function ob_print_r($what) {
	ob_start ();
	print_r ( $what );
	$c = ob_get_contents ();
	ob_end_clean ();
	return $c;
}

// function getPassedArgs($str, $from = null) {
// 	if ($from === null) {
// 		$from = $_REQUEST;
// 	}
// 	if (! is_array ( $str )) {
// 		$str = str_replace ( " ", "", $str );
// 		$ret = new stdClass ();
// 		$bits = explode ( ",", $str );
// 	}

// 	foreach ( $bits as $bit ) {
// 		// TODO: sanitise these some how
// 		$ret->$bit = @ $from[$bit];
// 	}
// 	return $ret;
// }

// class JsonResponse {

// 	function __construct() {
// 		$this->message = "";
// 		$this->console = "";
// 	}
// }

// function startJsonRespose() {
// 	global $database_domain;
// 	ob_start ();
// 	$ret = new JsonResponse ();
// 	return $ret;
// }

// function endJsonRespose($ret, $success = true) {
// 	global $ga_urchin_id;

// 	// TODO: Make this work
// 	// if ($ga_urchin_id != "") {
// 	// // https://developers.google.com/analytics/devguides/collection/protocol/v1/parameters
// 	// $ga = new stdClass ();
// 	// $ga->v = 1;
// 	// $ga->tid = $ga_urchin_id;
// 	// $ga->ds="web";
// 	// $ga->t="event";
// 	// $ga->ec = "API";
// 	// $ga->ea = basename($_SERVER["PATH_INFO"], ".php");
// 	// $ga->dl = $_SERVER["HTTP_HOST"]."/".$_SERVER["PATH_INFO"];
// 	// $ga->z = date("YmdHis"); // must be last
// 	// $q = http_build_query($ga);
// 	// $url = "https://www.google-analytics.com/collect?".$q;
// 	// echo file_get_contents($url);
// 	// }
// 	$ret->console = trim ( ob_get_contents () );
// 	if (strlen ( $ret->console ) && strpos ( $ret->console, "\n" ) !== FALSE) {
// 		$ret->console = explode ( "\n", $ret->console );
// 	}
// 	ob_end_clean ();

// 	$ret->success = $success;
// 	if (! isset ( $ret->status )) {
// 		$ret->status = ($success ? "ok" : "error");
// 	}
// 	$json = json_encode ( $ret );

// 	if (json_last_error () !== JSON_ERROR_NONE) {
// 		$json = json_encode ( array (
// 				"success" => false,
// 				"status" => "error",
// 				"message" => json_last_error_msg (),
// 				"console" => explode ( "\n", utf8_encode ( ob_print_r ( $ret ) ) )
// 		) );
// 	}
// 	header ( 'Content-type: application/json; charset=UTF-8' );
// 	echo $json;
// 	die ();
// }

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
				if ($extensoes[$i][0] == "*") {
					$extensoes[$i] = "." . $extensoes[$i];
				}
				if (preg_match ( "/" . $extensoes[$i] . "/i", $file )) {
					// if (ereg("\.+" . $extensoes[$i] . "$", $file)) {
					$files[] = $dirname . "/" . $file;
				}
			}
		}
	}

	@ closedir ( $dirname );
	sort ( $files );
	return $files;
}

function includeDirectory($d, $ext="php") {
	$ret = array ();
	$files = directoryListing ( $d, $ext );
	foreach ( $files as $file ) {
		// echo "loading $file<br />";
		if (! preg_match ( '/index.php$/', $file )) {
			$ret[] = $file;
		}
	}
	return $ret;
}

function tfn($v, $quote = '') {
	if ($v === true) return "true";
	if ($v === false) return "false";
	if ($v === null) return "null";
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
			// echo "file: '".$file."' - ".date("Y/m/d H:i:s", $mt)."\n";
			$mt = filemtime ( $file );
			if (is_dir ( $file )) {
				$n = newestFile ( $file );
				if ($n[0] > $mtime) {
					$mtime = $n[0];
					$mfile = $n[1];
				}
			} else if ($mt > $mtime) {
				$mtime = $mt;
				$mfile = $file;
			}
		}
	}

	// echo "newest file in '".$p."': '".$mfile."' - ".date("Y/m/d H:i:s", $mtime)."\n";
	return array (
			$mtime,
			$mfile,
			date ( "Y/m/d H:i:s", $mtime )
	);
}

$compress_page = true;

function startPage() {
	ob_start ();
}

function endPage($compress = null, $strip_comments = true) {
	if ($compress === null) {
		global $compress_page;
		$compress = $compress_page;
	}
	$odirty = ob_get_contents ();
	$dirty = $odirty;
	if ($strip_comments) {
		$dirty = preg_replace ( '/<!--(.|\s)*?-->/', '', $dirty );
	}
	ob_end_clean ();
	if ($compress) {
		libxml_use_internal_errors ( true );
		$x = new DOMDocument ();
		$x->loadHTML ( $dirty );
		$clean = $x->saveHTML ();
		// echo $dirty;
		echo "<!-- COIN73 - (c) 2020 - " . date ( 'Y' ) . " Nigel Johnson, all rights reserved -->\n";
		echo "<!-- uncompressed: " . number_format ( strlen ( $odirty ), 0 ) . " bytes, compressed: " . number_format ( strlen ( $clean ), 0 ) . " bytes -->";
		echo $clean;
	} else {
		echo $dirty;
	}
}

function obfuscateCode($email) {
	$alwaysEncode = array (
			'.',
			':',
			'@'
	);

	$result = '';

	// Encode string using oct and hex character codes
	for($i = 0; $i < strlen ( $email ); $i ++) {
		if (in_array ( $email[$i], $alwaysEncode ) || mt_rand ( 1, 100 ) < 75) {
			if (mt_rand ( 0, 1 )) {
				$result .= '&#' . ord ( $email[$i] ) . ';';
			} else {
				$result .= '&#x' . dechex ( ord ( $email[$i] ) ) . ';';
			}
		} else {
			$result .= $email[$i];
		}
	}

	return $result;
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
?>