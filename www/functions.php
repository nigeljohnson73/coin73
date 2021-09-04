<?php

function getAppName() {
	global $app_name;
	return $app_name;
}

function ob_print_r($what) {
	ob_start ();
	print_r ( $what );
	$c = ob_get_contents ();
	ob_end_clean ();
	return $c;
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

	return array (
			$mtime,
			$mfile,
			date ( "Y/m/d H:i:s", $mtime )
	);
}

function startPage() {
	ob_start ();
}

function endPage($compress = false, $strip_comments = true) {
	$odirty = ob_get_contents ();
	$dirty = $odirty;
	if ($strip_comments) {
		$dirty = preg_replace ( '/<!--(.|\s)*?-->/m', '', $dirty );
		$dirty = preg_replace('/^\w*[\r\n]+/m', '', $dirty);
	}
	ob_end_clean ();
	if ($compress) {
		libxml_use_internal_errors ( true );
		$x = new DOMDocument ();
		$x->loadHTML ( $dirty );
		$clean = $x->saveHTML ();
		// echo $dirty;
		if($_SERVER["SERVER_NAME"] == "localhost") {
			echo "<!-- RUNNING ON DEV HOST -->\n";
		}
		echo "<!-- COIN73 - (c) 2020 - " . date ( 'Y' ) . " Nigel Johnson, all rights reserved -->\n";
		echo "<!-- uncompressed: " . number_format ( strlen ( $odirty ), 0 ) . " bytes, compressed: " . number_format ( strlen ( $clean ), 0 ) . " bytes -->\n";
		echo "<!-- \n";
		print_r($_SERVER);
		echo "-->\n";
		echo $clean;
	} else {
		echo $dirty;
	}
}

// TODO: Put this in a JSON config along with the latest build etc
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