<?php
include_once (__DIR__ . "/DataStore.php");

class DebugStore extends DataStore {

	protected function __construct() {
		logger ( LL_INF, "DebugStore::DebugStore()" );

		parent::__construct ( "Debug" );

		$this->addField ( "created", "Float", true, true );
		$this->addField ( "time", "String", true );
		$this->addField ( "detail", "String" );

		$this->init ();
	}

	public function insert($arr) {
		$arr ["time"] = timestampFormat ( timestampNow (), "Y/m/d H:i:s" );
		$arr ["created"] = microtime ( true );
		return parent::insert ( $arr );
	}

	// https://stackoverflow.com/questions/1820908/how-to-turn-off-the-eclipse-code-formatter-for-certain-sections-of-java-code
	// https://patorjk.com/software/taag/#p=display&f=Standard&t=Housekeeping
	// @formatter:off
	//  _   _                      _                   _
	// | | | | ___  _   _ ___  ___| | _____  ___ _ __ (_)_ __   __ _
	// | |_| |/ _ \| | | / __|/ _ \ |/ / _ \/ _ \ '_ \| | '_ \ / _` |
	// |  _  | (_) | |_| \__ \  __/   <  __/  __/ |_) | | | | | (_| |
	// |_| |_|\___/ \__,_|___/\___|_|\_\___|\___| .__/|_|_| |_|\__, |
	//                                          |_|            |___/
	// @formatter:on
	protected function _tidyUp() {
		logger ( LL_DBG, "DebugStore::tidyUp(): started" );
		$older = microtime ( true ) - (3 * 60 * 60);
		$gql = "SELECT * FROM " . $this->kind . " WHERE created < @key";
		$this->obj_store->query ( $gql, [ 
				'key' => $older
		] );
		while ( $arr_page = $this->obj_store->fetchPage ( transactionsPerPage () ) ) {
			logger ( LL_DBG, "DebugStore::tidyUp(): pulled " . count ( $arr_page ) . " records" );
			// $this->active_transactions = array_merge ( $this->active_transactions, $arr_page );
			$this->obj_store->delete ( $arr_page );
		}
		logger ( LL_DBG, "DebugStore::tidyUp(): complete" );
	}

	public static function tidyUp() {
		self::getInstance ()->_tidyUp ();
	}

	public static function log($str) {
		$arr = array ();
		$arr ["detail"] = $str;
		self::getInstance ()->insert ( $arr );
	}
}
?>