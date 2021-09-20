<?php
include_once (__DIR__ . "/DataStore.php");

class DebugStore extends DataStore {

	protected function __construct() {
		logger ( LL_INF, "DebugStore::DebugStore()" );

		parent::__construct ( "Debug" );

		$this->addField ( "debug_id", "String", true, true ); // indexed and key
		$this->addField ( "created", "Float", true );
		$this->addField ( "time", "String" );
		$this->addField ( "detail", "String" );

		$this->init ();
	}

	public function insert($arr) {
		$oarr = $arr; // Save it in case the insert fails!!

		$arr [$this->getKeyField ()] = GUIDv4 ();
		$arr ["created"] = microtime ( true );
		$arr ["time"] = timestampNow ();
		$arr = parent::insert ( $arr );

		if ($arr == false) {
			logger ( LL_ERR, "DebugStore::insert() - failed - regenerating key" );
			// Assume the key was broken and retry
			$arr = $oarr;
			$arr [$this->getKeyField ()] = GUIDv4 ();
			$arr ["created"] = microtime ( true );
			$arr ["time"] = timestampNow ();
			$arr = parent::insert ( $arr );
			if ($arr == false) {
				logger ( LL_ERR, "DebugStore::insert() - failed again - You got bigger problems" );
			}
			// if it's still bust... well we all screwed
		}
		// Nothing else to do...
		return $arr;
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
		$older = microtime ( true ) - (12 * 60 * 60);
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