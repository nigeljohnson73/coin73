<?php
// This api is called when the the user has requested a user creation providing username/password, toc approval as well as recaptcha details in $_POST
//
session_id ( getDataNamespace () );
session_start ();
$ret = startJsonResponse ();

logger ( LL_DBG, "ARGS:" );
logger ( LL_DBG, ob_print_r ( $args ) );
logger ( LL_DBG, "_POST[]:" );
logger ( LL_DBG, ob_print_r ( $_POST ) );
logger ( LL_DBG, "_SESSION[]:" );
logger ( LL_DBG, ob_print_r ( $_SESSION ) );

$success = true;
$message = "";

$data = ( object ) InfoStore::getAll ();

// protected function _getApi() {
// $tx = array ();
// $tx [] = minedSharesInfoKey ();
// $tx [] = circulationInfoKey ();
// $tx [] = lastBlockHashInfoKey ();
// $tx [] = blockCountInfoKey ();
// sort ( $tx );

// $data = InfoStore::getAll ();

// $rdata = array ();
// foreach ( $tx as $key ) {
// $rdata [$key] = @$data [$key];
// }
// return $rdata;
// }

// public static function getApi() {
// return InfoStore::getInstance ()->_getApi ();
// }

$ret->data = new StdClass ();
$key = circulationInfoKey ();
$lkey = str_replace ( "info_", "", $key );
$ret->data->$lkey = ( double ) ($data->$key);

$key = minedSharesInfoKey ();
$lkey = str_replace ( "info_", "", $key );
$ret->data->$lkey = ( int ) ($data->$key);

$key = blockCountInfoKey ();
$lkey = str_replace ( "info_", "", $key );
$ret->data->$lkey = ( int ) ($data->$key);

$key = lastBlockHashInfoKey ();
$lkey = str_replace ( "info_", "", $key );
$ret->data->$lkey = $data->$key;

endJsonResponse ( $response, $ret, $success, $message );
?>