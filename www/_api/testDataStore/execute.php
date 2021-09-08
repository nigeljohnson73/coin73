<?php

class TestStore extends DataStore {

	public function __construct() {
		echo "UserStore::UserStore()\n";

		parent::__construct ( "Test" );

		$this->addField ( "field_key", "String", true, true ); // indexed and key
		$this->addField ( "field_data_1", "String" );
		$this->addField ( "field_data_2", "String" );
		$this->addField ( "field_data_3", "String" );
		
		$this->init ();
	}
}

function testStartSection($title) {
	echo "\n\n\n";
	echo "###########################################################################\n";
	// echo "####\n";
	echo "####  " . $title . "\n";
	// echo "####\n";
	echo "###########################################################################\n";
	echo "####\n";
}

function testEndSection() {
	echo "####\n";
	echo "###########################################################################\n";
	echo "\n\n\n";
}

function testIt($obj, $bool, $passStr, $failStr) {
	global $testn;
	$testn = $testn + 1;

	echo "#### TEST-";
	echo ($testn < 10) ? "0" : "";
	echo ($testn < 100) ? "0" : "";
	echo $testn . ": ";
	echo $bool ? "PASS" : "FAIL";
	echo ": ";
	echo $bool ? $passStr : $failStr;
	echo "\n";

	if (! $bool) {
		if (is_string ( $obj )) {
			echo output ( $obj ) . "\n";
		} else {
			print_r ( output ( $obj ) );
		}
	}
	return $bool;
}

function output($x) {
	if ($x === null) {
		return "[null]";
	}
	if ($x === true) {
		return "[true]";
	}
	if ($x === false) {
		return "[false]";
	}
	return $x;
}

$store = new TestStore ();
$field_key = "TESTv1";
$field_data_1 = "Data 1";
$field_data_2 = "Data 2";
$field_data_3 = "Data 3";

/**
 * **********************************
 * Insert test
 */
$obj = array ();
$obj ["field_key"] = $field_key;
$obj = $store->insert ( $obj );
testStartSection ( "Insert standard object" );
testIt ( $obj, is_array ( $obj ), "insert suceeded", "insert failed" );
testIt ( $obj, isset ( $obj ["field_key"] ), "key field present", "key field absent" );
testIt ( $obj, $obj ["field_key"] == $field_key, "key field correct", "key field wrong" );
testEndSection ();

/**
 * **********************************
 * Insert duplicate test
 */
$obj = array ();
$obj ["field_key"] = $field_key;
$obj = $store->insert ( $obj );
testStartSection ( "Insert duplicate object" );
testIt ( $obj, $obj == false, "duplicate insert correctly rejected", "duplicate insert suceeded" );
testEndSection ();

/**
 * **********************************
 * Update
 */
$obj = array ();
$obj ["field_key"] = $field_key;
$obj ["field_data_1"] = $field_data_1;
$obj = $store->update ( $obj );
testStartSection ( "Update first field" );
testIt ( $obj, is_array ( $obj ), "update suceeded", "update failed" );
testIt ( $obj, isset ( $obj ["field_data_1"] ), "data_1 field present", "data_1 field absent" );
testIt ( $obj, $obj ["field_data_1"] == $field_data_1, "data_1 field correct", "data_1 field wrong" );
testEndSection ();

$obj = array ();
$obj ["field_key"] = $field_key;
$obj ["field_data_2"] = $field_data_2;
$obj = $store->update ( $obj );
testStartSection ( "Update second field" );
testIt ( $obj, is_array ( $obj ), "update suceeded", "update failed" );
testIt ( $obj, (isset ( $obj ["field_data_1"] ) || ($obj ["field_data_1"] == "")), "data_1 field not present", "data_1 field incorrectly exists" );
testIt ( $obj, isset ( $obj ["field_data_2"] ), "data_2 field present", "data_2 field absent" );
testIt ( $obj, $obj ["field_data_2"] == $field_data_2, "data_2 field correct", "data_2 field wrong" );
testEndSection ();

/**
 * **********************************
 * replace
 */
$obj = array ();
$obj ["field_key"] = $field_key;
$obj ["field_data_3"] = $field_data_3;
$obj = $store->update ( $obj );
testStartSection ( "Replace object with 3rd field" );
testIt ( $obj, is_array ( $obj ), "update suceeded", "update failed" );
testIt ( $obj, (isset ( $obj ["field_data_1"] ) || ($obj ["field_data_1"] == "")), "data_1 field not present", "data_1 field incorrectly exists" );
testIt ( $obj, (isset ( $obj ["field_data_2"] ) || ($obj ["field_data_2"] == "")), "data_2 field not present", "data_2 field incorrectly exists" );
testIt ( $obj, isset ( $obj ["field_data_3"] ), "data_3 field present", "data_3 field absent" );
testIt ( $obj, $obj ["field_data_3"] == $field_data_3, "data_3 field correct", "data_3 field wrong" );
testEndSection ();

/**
 * **********************************
 * Delete
 */
$obj = array ();
$obj ["field_key"] = $field_key;
$obj = $store->delete ( $obj );
testStartSection ( "Delete test object" );
testIt ( $obj, is_array ( $obj ), "delete succeeded", "delete failed" );
testEndSection ();

/**
 * **********************************
 * Delete non existent test
 */
$obj = array ();
$obj ["field_key"] = $field_key;
$obj = $store->delete ( $obj );
testStartSection ( "Delete non-exitent object" );
testIt ( $obj, $obj == false, "delete non-existant correctly failed", "delete non-existant incorectly suceeded" );
testEndSection ();

/**
 * **********************************
 * Update non-existant object
 */
$obj = array ();
$obj ["field_key"] = $field_key;
$obj ["field_data_1"] = $field_data_1;
$obj = $store->update ( $obj );
testStartSection ( "Update non-existent object" );
testIt ( $obj, !is_array ( $obj ), "update correctly failed", "update incorrectly suceeded" );
//testIt ( $obj, (isset ( $obj ["field_data_1"] ) || ($obj ["field_data_1"] == "")), "data_1 field not present", "data_1 field incorrectly exists" );
testEndSection ();

?>