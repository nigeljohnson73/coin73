<?php
include_once (__DIR__ . "/DataStore.php");

class DebugStore extends DataStore {

	protected function __construct() {
		logger ( LL_DBG, "DebugStore::DebugStore()" );

		parent::__construct ( "Debug" );

		$this->addField ( "created", "Float", true, true );
		$this->addField ( "time", "String", true );
		$this->addField ( "detail", "String" );

		$this->init ();
	}

	public static function insert($arr) {
		$arr ["time"] = timestampFormat ( timestampNow (), "Y/m/d H:i:s" );
		$arr ["created"] = microtime ( true );
		return parent::insert ( $arr );
	}

	public static function log($str) {
		$arr = array ();
		$arr ["detail"] = $str;
		self::getInstance ()->insert ( $arr );
		logger ( LL_DBG, $str );
	}
}
?>