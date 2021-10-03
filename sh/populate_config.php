<?php
include_once (__DIR__ . "/../www/functions.php");

$json = file_get_contents ( __DIR__ . "/../bundle.json" );
$rep = ( array ) json_decode ( $json );

$keys = KeyStore::getKeys ( coinbaseName () );
$rep ["coinbase_public_key"] = $keys->public;
$rep ["coinbase_private_key"] = $keys->private;

ksort ( $rep );
print_r ( $rep );

$files = directoryListing ( __DIR__ . "/../www/", ".php" );
foreach ( $files as $filename ) {
	//echo "Got '" . basename ( $filename ) . "'\n";
	if (strpos ( basename ( $filename ), "config_" ) === 0) {
		echo "Processing '" . basename ( $filename ) . "'\n";
		$data = file_get_contents ( $filename );
		$ndata = $data;

		foreach ( $rep as $k => $v ) {
			$data = str_replace ( "{" . strtoupper ( $k ) . "}", $v, $data );
		}

		if ($data == $ndata) {
			echo "    No change\n";
		} else {
			echo "    **** File updated\n";
			file_put_contents ( $filename, $data );
		}
	}
}
?>
