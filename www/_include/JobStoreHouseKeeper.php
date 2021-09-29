<?php

class JobStoreHouseKeeper extends JobStore {

	protected function __construct() {
		logger ( LL_DBG, "JobStoreHouseKeeper::JobStoreHouseKeeper()" );
		parent::__construct ();
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
		logger ( LL_DBG, "JobStore::tidyUp(): started" );
		$older = microtime ( true ) - (minerSubmitMaxSeconds () + 1);
		$gql = "SELECT * FROM " . $this->kind . " WHERE created < @key";
		$this->obj_store->query ( $gql, [ 
				'key' => $older
		] );
		while ( $arr_page = $this->obj_store->fetchPage ( transactionsPerPage () ) ) {
			logger ( LL_DBG, "JobStore::tidyUp(): pulled " . count ( $arr_page ) . " records" );
			// $this->active_transactions = array_merge ( $this->active_transactions, $arr_page );
			$this->obj_store->delete ( $arr_page );
		}
		logger ( LL_DBG, "JobStore::tidyUp(): complete" );
	}

	protected function _reset() {
		$gql = "SELECT * FROM " . $this->kind;
		$this->obj_store->query ( $gql );
		while ( $arr_page = $this->obj_store->fetchPage ( transactionsPerPage () ) ) {
			logger ( LL_DBG, $this->kind . "Store::reset(): deleting " . count ( $arr_page ) . " records" );
			$this->obj_store->delete ( $arr_page );
		}
	}

	public static function tidyUp() {
		self::getInstance ()->_tidyUp ();
	}

	public static function __reset() {
		self::getInstance ()->_reset ();
	}
}
?>